<?php

namespace xecon;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use xecon\utils\FolderDatabase;

class Main extends PluginBase implements Listener{
	/** @var  string directory where economic entity information is stored` */
	private $edir;
	/** @var  FolderDatabase $db */
	private $db;
	/** @var Session[] $sessions */
	private $sessions = [];
	public function onEnable(){
		$this->mkdirs();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
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
	/**
	 * @return self
	 */
	public static function get(){
		return Server::getInstance()->getPluginManager()->getPlugin("xEcon");
	}
}
