<?php

namespace xecon\log;

use xecon\account\Account;
use xecon\Main;

class SQLite3LogProvider extends LogProvider{
	public function __construct(Main $main, $path){
		parent::__construct($main);
		$this->db = new \SQLite3($main->getDataFolder().$path);
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
	/**
	 * @param Account $from
	 * @param Account $to
	 * @param $amount
	 * @param $details
	 */
	public function logTransaction(Account $from, Account $to, $amount, $details){
		// TODO: Implement logTransaction() method.
	}
	/**
	 * @param $ftype
	 * @param $fname
	 * @param $facc
	 * @param $ttype
	 * @param $tname
	 * @param $tacc
	 * @param $minAmount
	 * @param $maxAmount
	 * @param $ftime
	 * @param $ttime
	 * @param $fromToOper
	 * @return array
	 */
	public function getTransactions($ftype, $fname, $facc, $ttype, $tname, $tacc, $minAmount, $maxAmount, $ftime, $ttime, $fromToOper){
		// TODO: Implement getTransactions() method.
	}
}
