<?php

namespace xecon\entity;

use pocketmine\Player;
use xecon\Main;

class PlayerEnt{
	use Entity;
	private $player;
	const ACCOUNT_CASH = "Cash";
	const ACCOUNT_BANK = "Bank";
	public function __construct($player, Main $main){
		$this->player = $player;
		if($player instanceof Player){
			$name = strtolower($player->getName());
		}
		else{
			$name = strtolower($player);
		}
		$this->initializeXEconEntity($this->getFolderByName($name), $main);
	}
	public function onQuit(){
		$this->save();
	}
	public function initDefaultAccounts(){
		$main = $this->main;
		$this->addAccount(self::ACCOUNT_BANK, $main->getDefaultBankMoney(), $main->getMaxBankMoney());
		$this->addAccount(self::ACCOUNT_CASH, $main->getDefaultCashMoney(), $main->getMaxCashMoney());
	}
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
		return "Player";
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
	public function __destruct(){
		$this->save();
	}
}
