<?php

namespace xecon\entity;

use pocketmine\Player;
use xecon\Main;

class PlayerEnt{
	use Entity;
	/** @var Player|string */
	private $player;
	const ACCOUNT_CASH = "Cash";
	const ACCOUNT_BANK = "Bank";
	const ABSOLUTE_PREFIX = "Player";
	public function __construct($player, Main $main){
		$this->player = $player;
		$this->initializeXEconEntity($main);
	}
	public function onQuit(){
//		$this->save();
	}
	protected function initDefaultAccounts(){
		$this->addAccount(self::ACCOUNT_BANK, 0, $this->getMain()->getMaxBankMoney(), -$this->getMain()->getMaxBankOverdraft());
		$this->addAccount(self::ACCOUNT_CASH, 0, $this->getMain()->getMaxCashMoney());
		$this->getMain()->touchIP($this);
	}
	/**
	 * @return Player|string
	 */
	public function getPlayer(){
		return $this->player;
	}
	public function getName(){
		if($this->player instanceof Player){
			return strtolower($this->player->getName());
		}
		return strtolower($this->player);
	}
	public function getAbsolutePrefix(){
		return self::ABSOLUTE_PREFIX;
	}
	public function getClass(){
		return "xecon\\entity\\PlayerEnt";
	}
	public function getInventory($name){
		if(!($this->player instanceof Player)){
			return null;
		}
		switch($name){
			case "cash":
				return $this->player->getInventory();
			default:
				return null;
		}
	}
	public function sendMessage($msg){
		if(!($this->player instanceof Player)){
			return false;
		}
		$this->player->sendMessage($msg);
		return true;
	}
	public function hasInstance(){
		return ($this->player instanceof Player);
	}
	public function release(){ // WeakRef functions :P
		$this->player = $this->player->getName();
	}
	public function acquire(Player $player = null){ // WeakRef functions :P
		if($player === null){
			$player = $this->getMain()->getServer()->getPlayerExact($this->getName());
			if(!($player instanceof Player)){
				return false;
			}
		}
		$this->player = $player;
		return true;
	}
}
