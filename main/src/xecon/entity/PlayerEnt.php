<?php

namespace xecon\entity;

use pocketmine\Player;
use xecon\XEcon;

class PlayerEnt{
	use Entity;
	/** @var \WeakRef<Player>|string */
	private $player;
	private $name;
	const ACCOUNT_CASH = "Cash";
	const ACCOUNT_BANK = "Bank";
	const ABSOLUTE_PREFIX = "Player";
	public function __construct($player, XEcon $main){
		if($player instanceof Player){
			$this->player = new \WeakRef($player);
			$this->name = $player->getName();
		}
		else{
			$this->player = $player;
			$this->name = $player;
		}
		$this->initializeXEconEntity($main);
	}
	public function onQuit(){
		$this->save();
		$this->release();
	}
	public function initializeDefaultAccounts(){
		$this->addAccount(self::ACCOUNT_BANK, 0, $this->getMain()->getMaxBankMoney(), -$this->getMain()->getMaxBankOverdraft());
		$this->addAccount(self::ACCOUNT_CASH, 0, $this->getMain()->getMaxCashMoney());
		if($this->valid()){
			$this->getMain()->getDataProvider()->checkPlayer($this);
		}
	}
	public function getName(){
		return strtolower($this->name);
	}
	public function check(){
		if($this->player instanceof \WeakRef){
			if(!$this->player->valid()){
				$this->getMain()->getLogger()->debug("Lost object reference for player $this->name");
				$this->player = $this->name;
			}
		}
	}
	public function release(){
		$this->player = $this->name;
	}
	public function getAbsolutePrefix(){
		return self::ABSOLUTE_PREFIX;
	}
	public function getInventory($name){
		$this->check();
		if($this->player instanceof \WeakRef){
			$player = $this->player->get();
		}
		else{
			$player = $this->getMain()->getServer()->getOfflinePlayer($this->name);
		}
		switch($name){
			case "cash":
				return $player->getInventory();
			default:
				return null;
		}
	}
	public function sendMessage($msg){
		$this->check();
		if($this->player instanceof \WeakRef){
			$this->player->get()->sendMessage($msg);
			return true;
		}
		return false;
	}
	public function valid(){
		$this->check();
		return ($this->player instanceof \WeakRef);
	}
	public function acquire(){
		$this->check();
		if(!($this->player instanceof \WeakRef)){
			$player = $this->getMain()->getServer()->getPlayerExact($this->name);
			if($player instanceof Player){
				$this->getMain()->getLogger()->debug("Found object reference for player $this->name");
				$this->player = new \WeakRef($player);
			}
		}
	}
	/**
	 * @return Player|string
	 */
	public function getPlayer(){
		$this->acquire();
		if($this->player instanceof \WeakRef){
			return $this->player->get();
		}
		return $this->player;
	}
	public function get(){
		return $this->getPlayer();
	}
}
