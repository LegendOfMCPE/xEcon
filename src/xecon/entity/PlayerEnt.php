<?php

namespace xecon\entity;

use pocketmine\Player;
use xecon\account\Account;
use xecon\Main;

class PlayerEnt extends Entity{
	/** @var \pocketmine\Player */
	private $player;
	const ACCOUNT_CASH = "Cash";
	const ACCOUNT_BANK = "Bank";
	public function __construct(Player $player){
		$this->player = $player;
		parent::__construct($this->getFolderByName($player->getName()), null);
	}
	public function initDefaultAccounts(){
		$main = Main::get();
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
		return "XECON_PLAYER_ENT";
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
}
