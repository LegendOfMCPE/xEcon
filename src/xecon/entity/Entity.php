<?php

namespace xecon\entity;

use xecon\Main;

abstract class Entity{
	protected $accounts = [];
	public abstract function sendMessage();
	public abstract function getAbsName(); // absolute, unique, constant lowercase name
	public function __construct(array $data){
		
	}
	public final function getID(){
		if(!isset($this->id)){
			// TODO get next EEID (Economic Entity IDentifier)
		}
	}
	public function toArray(){
		$data = [
			"type" => get_class($this),
			"accounts" => [],
			"eid" => 
		];
		foreach($this->accounts as $laid => $account){
			$data["account"][$laid] = $account->toArray();
		}
		return $data;
	}
	protected function onMoneyAdded(&$amount, $AID){
		return true; // false to prevent adding
	}
	public function addMoney($laid, $amount){ // Local Account IDentifier
		if($this->onMoneyAdded($amount, $laid) === false){
			return;
		}
		$this->accounts[$laid]->add($amount);
	}
	public final static function getEntType(){
		$type = static::getAbsEntityType();
		if(@strlen($type) !== 0x10){
			trigger_error("Unexpected returned value of a subclass of xEcon\\Entity: return value must be a 16-byte string", E_USER_ERROR);
			return "MALDEFINED_TYPE>";
		}
		return $type;
	}
	protected abstract static function getAbsEntityType();
	/**
	 * Inventory to save money into
	 * @return \pocketmine\inventory\Inventory
	 */
	public function getMoneyInventory();
}
