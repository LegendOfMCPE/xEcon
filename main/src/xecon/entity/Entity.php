<?php

namespace xecon\entity;

use xecon\account\Account;
use xecon\account\Loan;
use xecon\Main;

trait Entity{
	/** @var string */
	private $folder;
	/** @var Account[] */
	protected $accounts = [];
	/** @var Account[] */
	protected $liabilities = [];
	protected $loans = [];
	/** @var Main */
	protected $main;
	protected function initializeXEconEntity($folder, Main $main){
		$this->folder = $folder;
		if(!is_dir($folder)){
			$this->initAsDefault();
		}
		else{
			$this->init();
		}
		$this->main = $main;
		$this->getMain()->addEntity($this);
	}
	public function finalize(){
		$this->save();
	}
	private function init(){
		$data = json_decode(file_get_contents($this->getFolder()."general.json"));
		foreach($data["accounts"] as $account=>$data){
			$this->accounts[$account] = Account::constructFromArray($account, $this, $data);
		}
	}
	private function initAsDefault(){
		$this->initDefaultAccounts();
	}
	public function getInventory($account){
		return $this->accounts[$account]->getInventory();
	}
	public function getFolder(){
		return $this->folder;
	}
	/**
	 * @return Main
	 */
	public function getMain(){
		return $this->main;
	}
	protected function getFolderByName($name){
		return $this->main->getEntDir().$this->getAbsolutePrefix()."@#@!%".$name."/"; // how could I forget the slash...
	}
	protected function addAccount($name, $defaultAmount, $maxContainable = PHP_INT_MAX, $minAmount = 0){
		$name = strtolower($name);
		$this->accounts[$name] = new Account($name, $defaultAmount, $this);
		$this->accounts[$name]->setMaxContainable($maxContainable);
		$this->accounts[$name]->setMinAmount($minAmount);
	}
	protected function addLiability($name, $maxAmount, $default = 0){
		$this->liabilities[$name] = new Account($name, $default, $this, null);
		$this->liabilities[$name]->setMaxContainable($maxAmount);
		$this->liabilities[$name]->setIsLiability(true);
	}
	public function addLoan(Account $from, $amount, $due){
		$loan = new Loan($from, $amount, $this, $due);
		$this->liabilities[$loan->getName()] = $loan;
	}
	public function save(){
//		file_put_contents($this->folder."hook.json", json_encode(get_class($this)));
		$data = [];
		$data["accounts"] = [];
		foreach($this->accounts as $acc){
			$data["accounts"][$acc->getName()] = $acc->toArray();
		}
		foreach($this->liabilities as $acc){
			$data["accounts"][$acc->getName()] = $acc->toArray();
		}
		file_put_contents($this->folder."general.json", json_encode($data, JSON_PRETTY_PRINT|JSON_BIGINT_AS_STRING));
	}
	public function delete(){
		$directory = dir($dir = $this->folder);
		while(($file = $directory->read()) !== false){
			unlink($dir.$file);
		}
		$directory->close();
		rmdir($dir);
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
	public function getNetBalance(){ // no idea why I put this here. well, this might get handy later.
		$balance = 0;
		foreach($this->accounts as $acc){
			$balance += $acc->getAmount();
		}
		foreach($this->liabilities as $l){
			$balance -= $l->getAmount();
		}
		return $balance;
	}
	public function getUniqueName(){
		return $this->getAbsolutePrefix()."/".$this->getName();
	}
	public abstract function getName();
	public abstract function getAbsolutePrefix();
	public abstract function sendMessage($msg);
	protected abstract function initDefaultAccounts();
	public function __destruct(){
		$this->save();
	}
}
