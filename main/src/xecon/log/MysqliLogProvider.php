<?php

namespace xecon\log;

class MysqliLogProvider extends LogProvider{
	/**
	 * @param Transaction $tsctn
	 */
	public function logTransaction(Transaction $tsctn){
		// TODO: Implement logTransaction() method.
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
		// TODO: Implement getTransactions() method.
	}
}
