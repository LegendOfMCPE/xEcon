<?php

namespace xecon\entity;

use pocketmine\Player;
use xecon\Main;

class PlayerEnt{
	use Entity{
		Entity::__construct as private __parentConstruct;
	}
	/** @var \pocketmine\Player */
	private $player;
	const ACCOUNT_CASH = "Cash";
	const ACCOUNT_BANK = "Bank";
	public function __construct(Player $player, Main $main){
		$this->player = $player;
		$this->__parentCostruct($this->getFolderByName($player->getName()), $main);
	}
	public function onQuit(){
		$this->finalize();
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
		return "PlayerEntity";
	}
	public function getAbsolutePrefix(){
		return "xEcon_player_ent";
	}
	public function getClass(){
		return "xecon\\entity\\PlayerEnt";
	}
	public function getInventory($name){
		switch($name){
			case "cash":
				return $this->player->getInventory();
			default:
				return null;
		}
	}
	public function sendMessage($msg){
		$this->player->sendMessage($msg);
		return true;
	}
}
