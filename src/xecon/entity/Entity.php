<?php

namespace xecon\entity;

use xecon\account\Account;
use xecon\Main;

trait Entity{
	/** @var string */
	private $folder;
	/** @var Account[] */
	protected $accounts = [];
	protected function __construct($folder){
		$this->folder = $folder;
		if(!is_dir($folder)){
			$this->initAsDefault();
		}
		else{
			$this->init();
		}
	}
	private function init(){
		$data = json_decode(file_get_contents($this->getFolder()."general.json"));
		foreach($data["accounts"] as $account=>$data){
			$this->accounts[$account] = new Account($account, $data["amount"], $this->getInventory($account));
			$this->accounts[$account]->setMaxContainable($data["max-containable"]);
		}
	}
	private function initAsDefault(){
		$this->initDefaultAccounts();
	}
	protected abstract function initDefaultAccounts();
	public function getInventory($account){
		return $this->accounts[$account]->getInventory();
	}
	public function getFolder(){
		return $this->folder;
	}
	protected function getFolderByName($name){
		return Main::get()->getEntDir().$this->getAbsolutePrefix().$name;
	}
	protected function addAccount($name, $defaultAmount, $maxContainable = PHP_INT_MAX){
		$this->accounts[$name] = new Account($name, $defaultAmount, $this, $this->getInventory($name));
		$this->accounts[$name]->setMaxContainable($maxContainable);
	}
	public function save(){
//		file_put_contents($this->folder."hook.json", json_encode(get_class($this)));
		$data = [];
		$data["accounts"] = [];
		foreach($this->accounts as $acc){
			$data["accounts"][$acc->getName()] = [
				"amount" => $acc->getAmount(),
				"max-containable" => $acc->getMaxContainable()
			];
		}
		file_put_contents($this->folder."general.json", json_encode($data));
	}
	public abstract function getName();
	public abstract function getAbsolutePrefix();
	public function getAccount($name){
		return $this->accounts[$name];
	}
	public function getAccounts(){
		return $this->accounts;
	}
	public abstract function sendMessage();
}
