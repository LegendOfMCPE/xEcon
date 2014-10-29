<?php

if(!function_exists("yaml_parse_file")){
	die("YAML extension not found");
}

$here = rtrim(realpath("."), "/\\") . "";

if(!is_dir("$here/EconomyAPI")){
	echo "EconomyAPI data folder is not found at $here! Please run this script from the plugins folder.\n";
}

$opts = getopt("", ["type::", "path::", "loanexp::", "host::", "username::", "password::", "database::", "port::"]);

if(!isset($opts["type"])){
	$bin = PHP_BINARY;
	die(<<<EOU
If you would like to port to an SQLite3 database, run this:
$bin --type SQLite3 [--path <path>] [--loanexp <loan expiry>] [--maxcash <max cash>] [--maxbank <max bank>] [--minbank <min bank>]
        path: path to save inside the xEcon folder, default database.sq3
If you would like to port to a MySQL database, run this:
$bin --type MySQL --host <database address> --username <username> --password <password> --database <database name> [--port <port>] [--loanexp <loan expiry>] [--maxcash <max cash>] [--maxbank <max bank>] [--minbank <min bank>]
        port: port of the database, default 3306
    loan expiry: number of hours from now until all debts from the economys database are expired. 0 means instant expiry; -1 means forever (a long time, possibly many years), default -1
    max cash/bank: max amount of money allowed in each cash/bank account, default 1000/100000
    min bank: min amount of money allowed in each bank account (which is the amount of allowed bank overdraft times negative 1), default 0/-10
EOU
	);
}

switch(strtolower($opts["type"])){
	case "sqlite3":
		$dir = "$here/xEcon";
		if(!is_dir($dir)){
			mkdir($dir) or die("Cannot create xEcon folder");
		}
		$database = "database.sq3";
		if(isset($opts["path"])){
			$database = $opts["path"];
		}
		$path = "$dir/$database";
		if(file_exists($path) and !is_file($path)){
			die("$path is occupied by a non-file; please delete it or choose another path.");
		}
		$loanExpiry = PHP_EOL;
		if(isset($opts["loanexp"])){
			$loanExpiry = (int) $opts["loanexp"];
			if($loanExpiry === -1){
				$loanExpiry = PHP_EOL;
			}
		}
		$maxCash = 1000;
		if(isset($opts["maxcash"])){
			$maxCash = floatval($opts["maxcash"]);
		}
		$maxBank = 100000;
		if(isset($opts["maxbank"])){
			$maxBank = floatval($opts["maxbank"]);
		}
		$minBank = -10;
		if(isset($opts["minbank"])){
			$minBank = floatval($opts["minbank"]);
			if($minBank > 0){
				die("Bank minimum amount must not exceed zero");
			}
		}
		$db = new SQLite3($path);
		$db->exec("CREATE TABLE IF NOT EXISTS ents (
				ent_type TEXT,
				ent_name TEXT,
				register_time INTEGER,
				last_modify INTEGER
				);");
		$db->exec("CREATE TABLE IF NOT EXISTS ent_accounts (
				ent_type TEXT,
				ent_name TEXT,
				name TEXT,
				amount REAL,
				max_containable INTEGER,
				min_amount INT
				);");
		$db->exec("CREATE TABLE IF NOT EXISTS ent_loans (
				ent_type TEXT,
				ent_name TEXT,
				name TEXT,
				amount REAL,
				due INTEGER,
				increase_per_hour REAL,
				creation INTEGER,
				original_amount REAL,
				last_interest_update INTEGER,
				from_type TEXT,
				from_name TEXT,
				from_account TEXT
				);");
		$db->exec("CREATE TABLE IF NOT EXISTS ips (ip REAL PRIMARY KEY);");
		$import = mapEconomysData("$here/EconomyAPI");
		foreach($import as $player => $data){
			dbQuery($db, "INSERT INTO ents VALUES (:type, :name, :now, :now);", [
				"type" => "Player", // \xecon\entity\PlayerEnt::ABSOLUTE_PREFIX,
				"name" => strtolower($player),
				"now" => time()
			]);
			dbQuery($db, "INSERT INTO ent_accounts VALUES (:type, :name, :accname, :amount, :max, 0);", [
				"type" => "Player", // \xecon\entity\PlayerEnt::ABSOLUTE_PREFIX,
				"name" => strtolower($player),
				"accname" => "Cash", // \xecon\entity\PlayerEnt::ACCOUNT_CASH,
				"amount" => $data->cash,
				"max" => $maxCash,
			]);
			// TODO insert bank
			dbQuery($db, "INSERT INTO ent_loans VALUES (:type, :name, :accname, :amount, :due, :perhrinc, :creation, :origamount, :lastupdate, :fromtype, :fromname, :fromaccname", [
				"type" => "Player", // \xecon\entity\PlayerEnt::ABSOLUTE_PREFIX,
				"name" => strtolower($player),
				"accname" => "Cash", // \xecon\entity\PlayerEnt::ACCOUNT_CASH,
				"amount" => $data->debt,
				"due" => time() + $loanExpiry,
				"perhrinc" => 0,
				"creation" => time(),
				"origamount" => $data->debt,
				"lastupdate" => time(),
				"fromtype" => "Server", // \xecon\entity\Service::TYPE,
				"fromname" => "Services", // \xecon\entity\Service::NAME,
				"fromaccname" => "BankLoanSource", // \xecon\entity\Service::ACCOUNT_LOANS,
			]);
		}
		break;
}

function dbQuery(SQLite3 $db, $query, $args){
	$op = $db->prepare($query);
	foreach($args as $key => $value){
		$op->bindValue(":$key", $value);
	}
	return $op->execute();
}
function mapEconomysData($dir){
	/** @var EconomysPlayerData[] $players */
	$players = [];
	$dir = rtrim($dir, "/\\");
	$bankData = yaml_parse_file("$dir/Bank.yml") or die("Economys Bank.yml file is corrupted");
	foreach($bankData["money"] as $player => $bank){
		$players[$k = strtolower($player)] = new EconomysPlayerData;
		$players[$k]->bank = $bank;
	}
	$moneyData = yaml_parse_file("$dir/Money.yml") or die("Economys Money.yml file is corrupted");
	foreach($moneyData["money"] as $player => $money){
		$k = strtolower($player);
		if(!isset($players[$k])){
			$players[$k] = new EconomysPlayerData;
		}
		$players[$k]->cash = $money;
	}
	foreach($moneyData["debt"] as $player => $debt){
		$k = strtolower($player);
		if(!isset($players[$k])){
			$players[$k] = new EconomysPlayerData;
		}
		$players[$k]->cash = $debt;
	}
	return $players;
}

class EconomysPlayerData{ // this is more a structure than a class :D
	public $cash = 0, $bank = 0, $debt = 0;
}
