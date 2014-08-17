<?php

namespace xecon\log;

use xecon\Main;

abstract class LogProvider{
	const O_OR = 0;
	const O_AND = 1;
	const O_XOR = 2;
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
	 * @param Transaction $tsctn
	 */
	public abstract function logTransaction(Transaction $tsctn);
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
	public abstract function getTransactions($ftype, $fname, $facc, $ttype, $tname, $tacc,
			$minAmount, $maxAmount, $ftime, $ttime, $fromToOper = self::O_OR);
}
