<?php

namespace xecon\entity;

use xecon\account\Account;
use xecon\account\Loan;
use xecon\XEcon;

trait Entity{
	/** @var Account[] */
	private $accounts = [];
	/** @var Loan[] */
	private $loans = [];
	/** @var XEcon */
	protected $main;
	protected function initializeXEconEntity(XEcon $main){
		$this->main = $main;
		$this->main->getDataProvider()->loadEntity($this);
		$this->getMain()->addEntity($this);
	}
	public function getInventory($account){
		return $this->accounts[$account]->getInventory();
	}
	/**
	 * @return XEcon
	 */
	public function getMain(){
		return $this->main;
	}
	/**
	 * @param string $name
	 * @param number $defaultAmount
	 * @param int|number $maxContainable
	 * @param int $minAmount
	 * @throws \InvalidArgumentException
	 */
	public function addAccount($name, $defaultAmount, $maxContainable = PHP_INT_MAX, $minAmount = 0){
		$name = strtolower($name);
		if(isset($this->accounts[$name])){
			throw new \InvalidArgumentException("Account $name already exists for $this");
		}
		$this->accounts[$name] = new Account($name, $defaultAmount, $this);
		$this->accounts[$name]->setMaxContainable($maxContainable);
		$this->accounts[$name]->setMinAmount($minAmount);
	}
	public function addLoan(Account $from, $amount, $due, $increasePerHour = 0){
		$loan = new Loan($from, $amount, $this, $due, $increasePerHour);
		$this->loans[$loan->getName()] = $loan;
	}
	public function addLoanRaw(Loan $loan){
		$this->loans[$loan->getName()] = $loan;
	}
	public function removeLoan(Loan $loan){
		if($loan->getOwner() !== $this){
			return $loan->getOwner()->removeLoan($loan);
		}
		if(!isset($this->loans[$loan->getName()])){
			return false;
		}
		unset($this->loans[$loan->getName()]);
		return true;
	}
	public function delete(){
		$this->getMain()->getDataProvider()->deleteEntity($this->getUniqueName());
	}
	/**
	 * @param $name
	 * @return bool|Account
	 */
	public function getAccount($name){
		$name = strtolower($name);
		return isset($this->accounts[$name]) ? $this->accounts[$name]:false;
	}
	public function getAccounts(){
		return $this->accounts;
	}
	public function getLoans(){
		return $this->loans;
	}
	public function getNetBalance(){ // no idea why I put this here. well, this might get handy later.
		$balance = 0;
		foreach($this->accounts as $acc){
			$balance += $acc->getAmount();
		}
		foreach($this->loans as $l){
			$balance -= $l->getAmount();
		}
		return $balance;
	}
	public function getUniqueName(){
		return "{$this->getAbsolutePrefix()}/{$this->getName()}";
	}
	public abstract function getName();
	public abstract function getAbsolutePrefix();
	public abstract function sendMessage($msg);
	public final function initDefaultAccounts(){
		$this->getMain()->getLogger()->info("Initializing new economic entity: {$this->getUniqueName()}");
		$this->initializeDefaultAccounts();
	}
	protected abstract function initializeDefaultAccounts();
	public function __destruct(){
		$this->save();
	}
	public function save(){
		$this->getMain()->getDataProvider()->saveEntity($this);
	}
	public function __toString(){
		return $this->getUniqueName();
	}
}
