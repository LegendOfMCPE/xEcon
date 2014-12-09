<?php

namespace xecon\provider;

use xecon\account\Loan;
use xecon\entity\Entity;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\XEcon;

class SQLite3DataProvider extends DataProvider{
	/** @var \SQLite3 */
	private $db;
	public function __construct(XEcon $plugin, array $args){
		parent::__construct($plugin);
		$this->db = new \SQLite3($plugin->getDataFolder() . $args["path"]);
		$this->db->exec("CREATE TABLE IF NOT EXISTS ents (
				ent_type TEXT,
				ent_name TEXT,
				register_time INTEGER,
				last_modify INTEGER
				);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS ent_accounts (
				ent_type TEXT,
				ent_name TEXT,
				name TEXT,
				amount REAL,
				max_containable INTEGER,
				min_amount INT
				);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS ent_loans (
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
		$this->db->exec("CREATE TABLE IF NOT EXISTS ips (ip REAL PRIMARY KEY);");
	}
	public function loadEntity(Entity $entity){
		if($this->existsEntity($entity->getAbsolutePrefix(), $entity->getName())){
			$result = $this->db->query("SELECT * FROM ent_accounts
					WHERE ent_type = '{$this->db->escapeString($entity->getAbsolutePrefix())}'
					AND ent_name = '{$this->db->escapeString($entity->getName())}';");
			while(is_array($data = $result->fetchArray(SQLITE3_ASSOC))){
				$entity->addAccount($data["name"], $data["amount"], $data["max_containable"], $data["min_amount"]);
			}
			$result = $this->db->query("SELECT * FROM ent_loans
					WHERE ent_type = '{$this->db->escapeString($entity->getAbsolutePrefix())}'
					AND ent_name = '{$this->db->escapeString($entity->getName())}';");
			while(is_array($data = $result->fetchArray(SQLITE3_ASSOC))){
				switch($type = $data["from_type"]){
					case PlayerEnt::ABSOLUTE_PREFIX:
						$from = $this->getMain()->getPlayerEnt($data["from_name"])->getAccount($data["from_account"]);
						break;
					case Service::TYPE:
						if($data["from_name"] === Service::NAME){
							$from = $this->getMain()->getService()->getService($data["from_account"]);
							break;
						}
					default:
						throw new \Exception("Unsupported creditor type: $type");
				}
				$entity->addLoanRaw(new Loan($from, $data["amount"], $entity, $data["due"],
						$data["increase_per_hour"], $data["name"], $data["creation"],
						$data["original_amount"], $data["last_interest_update"]));
			}
		}
		else{
			$entity->initDefaultAccounts();
		}
	}
	public function saveEntity(Entity $entity){
		$now = time();
		if($this->existsEntity($entity->getAbsolutePrefix(), $entity->getName())){
			$this->db->query("UPDATE ents SET register_time = $now " .
					"WHERE ent_type = '{$this->db->escapeString($entity->getAbsolutePrefix())}'
					AND ent_name = '{$this->db->escapeString($entity->getName())}';");
		}
		else{
			$this->db->query("INSERT INTO ents (ent_type, ent_name, register_time, last_modify) VALUES (
					'{$this->db->escapeString($entity->getAbsolutePrefix())}',
					'{$this->db->escapeString($entity->getName())}',
					$now, $now
					);");
		}
		$this->db->query("DELETE FROM ent_accounts
				WHERE ent_type = '{$this->db->escapeString($entity->getAbsolutePrefix())}'
				AND ent_name = '{$this->db->escapeString($entity->getName())}';");
		foreach($entity->getAccounts() as $acc){
			$this->db->query("INSERT INTO ent_accounts VALUES (
					'{$this->db->escapeString($entity->getAbsolutePrefix())}',
					'{$this->db->escapeString($entity->getName())}',
					'{$this->db->escapeString($acc->getName())}',
					{$acc->getAmount()},
					{$acc->getMaxContainable()},
					{$acc->getMinAmount()}
					);");
		}
		$this->db->query("DELETE FROM ent_loans
				WHERE ent_type = '{$this->db->escapeString($entity->getAbsolutePrefix())}'
				AND ent_name = '{$this->db->escapeString($entity->getName())}';");
		foreach($entity->getLoans() as $loan){
			$loan->updateInterest();
			$this->db->query("INSERT INTO ent_loans VALUES (
					'{$this->db->escapeString($entity->getAbsolutePrefix())}',
					'{$this->db->escapeString($entity->getName())}',
					'{$this->db->escapeString($loan->getName())}',
					{$loan->getAmount()},
					{$loan->getDue()},
					{$loan->getHourlyIncrease()},
					{$loan->getCreationTime()},
					{$loan->getOriginalAmount()},
					{$loan->getLastInterestUpdate()},
					'{$this->db->escapeString($loan->getCreditor()->getEntity()->getAbsolutePrefix())}',
					'{$this->db->escapeString($loan->getCreditor()->getEntity()->getName())}',
					'{$this->db->escapeString($loan->getCreditor()->getName())}'
					);");
		}
	}
	public function deleteEntity($uniqueName){
		$tokens = explode("/", $uniqueName);
		if($this->existsEntity($tokens[0], $tokens[1])){
			$this->db->exec("DELETE FROM ents WHERE ent_type = '{$this->db->escapeString($tokens[0])}'
				AND ent_name = '{$this->db->escapeString($tokens[1])}';");
			$this->db->exec("DELETE FROM ent_accounts WHERE ent_type = '{$this->db->escapeString($tokens[0])}'
				AND ent_name = '{$this->db->escapeString($tokens[1])}';");
			$this->db->exec("DELETE FROM ent_loans WHERE ent_type = '{$this->db->escapeString($tokens[0])}'
				AND ent_name = '{$this->db->escapeString($tokens[1])}';");
		}
	}
	private function existsEntity($type, $name){
		return is_array($this->db->query("SELECT register_time,last_modify FROM ents
				WHERE ent_type = '{$this->db->escapeString($type)}'
				AND ent_name = '{$this->db->escapeString($name)}';")->fetchArray(SQLITE3_ASSOC));
	}
	/**
	 * @param $ip
	 * @return bool whether the IP is already registered
	 */
	public function touchIP($ip){
		$op = $this->db->prepare("SELECT * FROM ips WHERE ip = :ip;");
		$op->bindValue(":ip", $bin = implode("", array_map(function($intStr){
			return chr(intval($intStr));
		}, explode(".", $ip))));
		$exists = is_array($op->execute()->fetchArray(SQLITE3_ASSOC));
		if($exists){
			return true;
		}
		$op = $this->db->prepare("INSERT INTO ips VALUES (:ip);");
		$op->bindValue(":ip", $bin);
		$op->execute();
		return false;
	}
	public function close(){
		$this->db->close();
	}
}
