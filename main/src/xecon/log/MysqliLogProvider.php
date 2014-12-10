<?php

namespace xecon\log;

use xecon\XEcon;

class MysqliLogProvider extends LogProvider{
	/** @var \mysqli */
	private $db;
	/** @var string */
	private $tbl;
	public function __construct(XEcon $main, \mysqli $mysqli, $tbl){
		parent::__construct($main);
		$this->db = $mysqli;
		$this->tbl = $tbl;
		$mysqli->query("CREATE TABLE IF NOT EXISTS $tbl (
				fromtype VARCHAR(255),
				fromname VARCHAR(255),
				fromaccount VARCHAR(255),
				totype VARCHAR(255),
				toname VARCHAR(255),
				toaccount VARCHAR(255),
				amount DOUBLE,
				details VARCHAR(65535),
				tmstmp INT
				);");
	}
	/**
	 * @param Transaction $tran
	 */
	public function logTransaction(Transaction $tran){
		$now = time();
		$this->db->query("INSERT INTO {$this->tbl} VALUES (
				{$this->esc($tran->getFromType())},
				{$this->esc($tran->getFromName())},
				{$this->esc($tran->getFromAccount())},
				{$this->esc($tran->getToType())},
				{$this->esc($tran->getToName())},
				{$this->esc($tran->getToAccount())},
				{$tran->getAmount()},
				{$tran->getDetails()},
				$now
				);");
	}
	private function esc($str){
		return "'{$this->db->escape_string($str)}'";
	}
	/**
	 * @param string $ftype
	 * @param string $fname
	 * @param string $facc
	 * @param string $ttype
	 * @param string $tname
	 * @param string $tacc
	 * @param double $minAmount
	 * @param double $maxAmount
	 * @param int $ftime
	 * @param int $ttime
	 * @param int $fromToOper
	 * @return Transaction
	 */
	public function getTransactions($ftype, $fname, $facc, $ttype, $tname, $tacc, $minAmount, $maxAmount, $ftime, $ttime, $fromToOper = self::O_OR){
		$query = "SELECT * FROM {$this->tbl} WHERE (amount BETWEEN $minAmount AND $maxAmount) AND (tmstmp BETWEEN $ftime AND $ttime)";
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
		while(is_array($data = $result->fetch_assoc())){
			$transactions[] = new Transaction($data["fromtype"], $data["fromname"], $data["fromaccount"],
				$data["totype"], $data["toname"], $data["toaccount"],
				$data["amount"], $data["details"], $data["timestamp"]);
		}
		$result->close();
		return $transactions;
	}
}
