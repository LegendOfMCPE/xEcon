<?php

namespace xecon\entity;

use xecon\XEcon;

class Service{
	use Entity{
		save as entity_save;
	}
	const TYPE = "Server";
	const NAME = "Services";
	const ACCOUNT_OPS = "Operators";
	const ACCOUNT_LOANS = "BankLoanSource";
	public function __construct(XEcon $main){
		$this->initializeXEconEntity($main);
	}
	public function sendMessage($msg, $level = \LogLevel::INFO){
		$this->getMain()->getLogger()->log($level, $msg);
	}
	public function initializeDefaultAccounts(){
		$bits = PHP_INT_SIZE << 3 - 1;
		$PHP_INT_MIN = 1 << $bits;
		$this->addAccount("Operators", PHP_INT_MAX >> 1, PHP_INT_MAX, $PHP_INT_MIN);
		$this->addAccount("BankLoanSource", PHP_INT_MAX >> 1, PHP_INT_MAX, $PHP_INT_MIN);
		$this->entity_save();
	}
	public function registerService($name){
		try{
			$this->addAccount($name, (int) ceil(PHP_INT_MAX / 2), PHP_INT_MAX, 1 << PHP_INT_SIZE << 3 - 1);
			$this->entity_save();
			return true;
		}
		catch(\InvalidArgumentException $e){
			return false;
		}
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
	public function refillAll(){
		foreach($this->getAccounts() as $account){
			$account->setAmount(PHP_INT_MAX >> 1);
		}
	}
	public function save(){}
}
