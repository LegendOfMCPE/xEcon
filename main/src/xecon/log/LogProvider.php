<?php

namespace xecon\log;

use xecon\account\Account;
use xecon\Main;

abstract class LogProvider{
	/** @var Main */
	private $main;
	public function __construct(Main $main){
		$this->main = $main;
	}
	/**
	 * @return Main
	 */
	public function getMain(){
		return $this->main;
	}
	/**
	 * @param Account $from
	 * @param Account $to
	 * @param $amount
	 * @param $details
	 */
	public abstract function logTransaction(Account $from, Account $to, $amount, $details);
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
	public abstract function getTransactions($ftype, $fname, $facc, $ttype, $tname, $tacc,
			$minAmount, $maxAmount, $ftime, $ttime, $fromToOper);
}
