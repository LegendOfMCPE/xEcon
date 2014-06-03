<?php

namespace xecon\entity;

use xecon\account\Account;
use xecon\Main;

abstract class Entity{
	/** @var  string */
	private $folder;
	/** @var EntityType  */
	private $type;
	protected function __construct($folder, EntityType $type){
		$this->folder = $folder;
		$this->type = $type;
		if(!is_dir($folder)){
			$this->initAsDefault();
		}
		else{
			$this->init();
		}
	}
	private function init(){
		$data = json_decode(file_get_contents($this->getFolder()."general.json"));
		foreach($data["accounts"] as $account=>$amount){
			$this->accounts[$account] = new Account($account, $amount, $this->getInventory());
		}
	}
	private function initAsDefault(){

	}
	public function getInventory(){
		return null;
	}
	public function getFolder(){
		return $this->folder;
	}
	public function getType(){
		return $this->type;
	}
	protected function getFolderByName($name){
		return Main::get()->getEntDir().$this->getType()->getAbsolutePrefix().$name;
	}
	public function save(){
		file_put_contents($this->folder."hook.json", json_encode(get_class($this)));
	}
	public abstract function getName();
	public abstract function getAbsolutePrefix();
//	public abstract function getClass();
}
