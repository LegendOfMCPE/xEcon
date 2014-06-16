<?php

namespace xecon;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use xecon\commands\MyCommandMap;
use xecon\commands\SetMoneySubcommand;
use xecon\commands\SetPlayerMoneySubcommand;

class Main extends PluginBase implements Listener{
	/** @var string directory where economic entity information is stored */
	private $edir;
	/** @var Session[] $sessions */
	private $sessions = [];
	/** @var Config */
	private $userConfig;
	/** @var MyCommandMap */
	private $subcommandMap;
	public function onEnable(){
		$this->mkdirs();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->userConfig = new Config($this->getDataFolder()."config.yml", Config::YAML, [ // If I shifted my right hand one key leftwards on the QWERTY keyboard, YAML would become TANK
			"player-default-bank-money" => 500,
			"player-bank-max-money" => 100000,
			"player-default-cash-money" => 100,
			"player-max-cash-money" => 1000,
		]);
		$this->subcommandMap = new MyCommandMap($this);
		$this->subcommandMap->register(new SetMoneySubcommand);
		$this->subcommandMap->register(new SetPlayerMoneySubcommand);
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
		$this->sessions[$evt->getPlayer()->getID()] = new Session($evt->getPlayer(), $this);
	}
	public function onQuit(PlayerQuitEvent $evt){
		$p = $evt->getPlayer();
		if(isset($this->sessions[$this->CID($p)])){
			$this->sessions[$this->CID($p)]->onQuit();
			unset($this->sessions[$this->CID($p)]);
		}
	}
	public function onCommand(CommandSender $sender, Command $cmd, $l, array $args){
		return $this->subcommandMap->run($sender, $args);
	}
	public function getUserConfig(){
		return $this->userConfig;
	}
	public function getSessions(){
		return $this->sessions;
	}
	public function getPlayerEntity(Player $player){
		return $this->sessions[$this->CID($player)]->getEntity();
	}
	public static function CID(Player $player){
		return $player->getID();
	}
}
