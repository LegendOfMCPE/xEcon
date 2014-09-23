<?php

namespace xecon\entity;

use xecon\XEcon;

class Service{
	use Entity;
	const TYPE = "Server";
	const NAME = "Services";
	const ACCOUNT_OPS = "Operators";
	const ACCOUNT_LOANS = "BankLoanSource";
	public function __construct(XEcon $main){
		$this->initializeXEconEntity($main);
	}
	public function sendMessage($msg, $level = \LogLevel::INFO){
		$this->main->getLogger()->log($level, $msg);
	}
	public function initDefaultAccounts(){
		$this->addAccount("Operators", (int) ceil(PHP_INT_MAX / 2), PHP_INT_MAX, 0, false);
		$this->addAccount("BankLoanSource", (int) ceil(PHP_INT_MAX / 2), PHP_INT_MAX, 0, false);
	}
	public function registerService($name){
		$this->addAccount($name, (int) ceil(PHP_INT_MAX / 2));
	}
	public function getService($name){
		return $this->getAccount($name);
	}
	public function getName(){
		return self::NAME;
	}
	public function getAbsolutePrefix(){
		return self::TYPE;
	}
}
