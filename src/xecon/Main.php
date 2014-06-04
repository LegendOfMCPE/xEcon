<?php

namespace xecon;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use xecon\commands\MyCommandMap;
use xecon\utils\FolderDatabase;

class Main extends PluginBase implements Listener{
	/** @var  string directory where economic entity information is stored` */
	private $edir;
	/** @var Session[] $sessions */
	private $sessions = [];
	/** @var Config */
	private $userConfig;
	public function onEnable(){
		$this->mkdirs();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->userConfig = new Config($this->getDataFolder()."config.properties", Config::PROPERTIES, [
			"player-default-bank-money" => 500,
			"player-bank-max-money" => 100000,
			"player-default-cash-money" => 100,
			"player-max-cash-money" => 1000,
		]);
		$this->subcmds = new MyCommandMap;
		// add register commands
	}
	public function getDefaultBankMoney(){
		return $this->userConfig->get("player-default-bank-money");
	}
	public function getDefaultCashMoney(){
		return $this->userConfig->get("player-default-cash-money");
	}
	public function getMaxBankMoney(){
		return $this->userConfig->get("player-max-bank-money");
	}
	public function getMaxCashMoney(){
		return $this->userConfig->get("player-max-cash-money");
	}
	private function mkdirs(){
		@mkdir($this->getDataFolder());
		@mkdir($this->edir = $this->getDataFolder()."entities database/");
	}
	public function getEntDir(){
		return $this->edir;
	}
	public function onJoin(PlayerJoinEvent $evt){
		$this->sessions[$evt->getPlayer()->CID] = new Session($evt->getPlayer());
	}
	public function onQuit(PlayerQuitEvent $evt){
		$p = $evt->getPlayer();
		if(isset($this->sessions[$p->CID])){
			$this->sessions[$p->CID]->onQuit();
			unset($this->sessions[$p->CID]);
		}
	}
	public function onCommand(CommandSender $sender, Command $cmd, $l, array $args){
		$output = "";
		$wrongUse = false;
		switch($cmd){
			case "xecon":

				break;
		}
		if($wrongUse){
			return false;
		}
		$sender->sendMessage($output);
		return true;
	}
	public function getUserConfig(){
		return $this->userConfig;
	}
	/**
	 * @return self
	 */
	public static function get(){
		return Server::getInstance()->getPluginManager()->getPlugin("xEcon");
	}
}
