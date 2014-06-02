<?php

namespace xecon;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command as Cmd;
use pocketmine\command\CommandSender as Issuer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent!
use pocketmine\plugin\PluginBase as PB;

use xecon\entity\PlayerEntity;
use xecon\utils\HttpLoop;

class Main extends PB implements Listener{
	const VERSION = 0; // current version constant

	const V_INITIAL = 0; // initial version constant

	public static $NAME = "xEcon";

	protected $cmdh;
	protected $sessions = null;
	public function onLoad(){
		self::$NAME = $this->getName();
	}
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->database = new Database;
		$this->database->load();
		HttpLoop::init();
	}
	public function getDatabasePath(){
		return $this->getDataFolder()."database/";
	}
	public function onJoin(PlayerJoinEvent $evt){
		$this->database->init($this->playerEnts[$evt->getPlayer()->CID] = new PlayerEntity($evt->getPlayer()));
	}
	public function onQuit(PlayerQuitEvent $evt){
		$this->database->fine($this->planerEnts[$evt->getPlayer()->CID]);
	}
	public function onCommand(Issuer $issuer, Cmd $cmd, $lbl, array $args){
		if(!isset($args[0])){
			return false;
		}
		$cmd = array_shift($args);
		switch($cmd){
		}
	}
	public static function get(){
		return Server::getInstance()->getPluginManager()->getPlugin(self::$NAME);
	}
}
