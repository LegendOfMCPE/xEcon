<?php

namespace xecon\log;

use xecon\XEcon;

class SQLite3LogProvider extends LogProvider{
	public function __construct(XEcon $main, $path){
		parent::__construct($main);
		$this->db = new \SQLite3($main->getDataFolder() . $path);
		$this->db->exec("CREATE TABLE IF NOT EXISTS transactions (
				fromtype TEXT,
				fromname TEXT,
				fromaccount TEXT,
				totype TEXT,
				toname TEXT,
				toaccount TEXT,
				amount REAL,
				details TEXT,
				tmstmp INTEGER
				);");
	}
	public function close(){
		$this->db->close();
	}
	public function logTransaction(Transaction $tsctn){
		$now = time();
		$this->db->exec("INSERT INTO transactions VALUES (
				{$this->esc($tsctn->getFromType())},
				{$this->esc($tsctn->getFromName())},
				{$this->esc($tsctn->getFromAccount())},
				{$this->esc($tsctn->getToType())},
				{$this->esc($tsctn->getToName())},
				{$this->esc($tsctn->getToAccount())},
				{$tsctn->getAmount()},
				{$tsctn->getDetails()},
				$now
				);");
	}
	public function esc($str){
		return "'{$this->db->escapeString($str)}'";
	}
	/**
	 * @param string $ftype
	 * @param string $fname
	 * @param string $facc
	 * @param string $ttype
	 * @param string $tname
	 * @param string $tacc
	 * @param int|double $minAmount
	 * @param int|double $maxAmount
	 * @param int $ftime
	 * @param int $ttime
	 * @param int $fromToOper
	 * @return Transaction
	 */
	public function getTransactions($ftype = null, $fname = null, $facc = null, $ttype = null, $tname = null, $tacc = null, $minAmount = 0, $maxAmount = PHP_INT_MAX, $ftime = 0, $ttime = PHP_INT_MAX, $fromToOper = self::O_OR){
		$query = "SELECT * FROM transactions WHERE (amount BETWEEN $minAmount AND $maxAmount) AND (tmstmp BETWEEN $ftime AND $ttime)";
		$fromQuery = null;
		if($ftype !== null or $fname !== null or $facc !== null){
			$fromQueries = [];
			if($ftype !== null){
				$fromQueries[] = "fromtype = {$this->esc($ftype)}";
			}
			if($fname !== null){
				$fromQueries[] = "fromname = {$this->esc($fname)}";
			}
			if($facc !== null){
				$fromQueries[] = "fromaccount = {$this->esc($facc)}";
			}
			$fromQuery = implode(" AND ", $fromQueries);
		}
		$toQuery = null;
		if($ftype !== null or $fname !== null or $facc !== null){
			$toQueries = [];
			if($ftype !== null){
				$toQueries[] = "totype = {$this->esc($ftype)}";
			}
			if($fname !== null){
				$toQueries[] = "toname = {$this->esc($fname)}";
			}
			if($facc !== null){
				$toQueries[] = "toaccount = {$this->esc($facc)}";
			}
			$toQuery = implode(" AND ", $toQueries);
		}
		if($fromQuery !== null and $toQuery !== null){
			switch($fromToOper){
				case self::O_OR:
					$query .= " AND (($fromQuery) OR ($toQuery))";
				case self::O_AND:
					$query .= " AND (($fromQuery) AND ($toQuery))";
				default:
					$query .= " AND (($fromQuery) XOR ($toQuery))";
			}
		}
		elseif($fromQuery === null xor $toQuery === null){
			if($fromQuery === null){
				$query .= " AND ($toQuery)";
			}
			else{
				$query .= " AND ($fromQuery)";
			}
		}
		$query .= ";";
		$result = $this->db->query($query);
		$transactions = [];
		while(is_array($data = $result->fetchArray(SQLITE3_ASSOC))){
			$transactions[] = new Transaction($data["fromtype"], $data["fromname"], $data["fromaccount"],
					$data["totype"], $data["toname"], $data["toaccount"],
					$data["amount"], $data["details"], $data["timestamp"]);
		}
		return $transactions;
	}
}
