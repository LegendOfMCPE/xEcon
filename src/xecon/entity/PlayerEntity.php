<?php

namespace xecon\entity;

class PlayerEntity extends Entity{
	public function __construct(Player $player){
		$this->p = $player;
	}
	public function onMoneyAdded(&$amount, $AID){
		return true;
	}
	public function sendMessage($message){
		$this->p->sendMessage($message);
	}
	public function getAbsName(){
		return "player ".strtolower($this->p->getName());
	}
	protected static function getAbsEntityType(){
		return "xEcon_plyr_norm>";
}
