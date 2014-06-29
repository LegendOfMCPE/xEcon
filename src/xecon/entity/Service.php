<?php

namespace xecon\entity;

use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use xecon\Main;

abstract class Service{
	use Entity;
	protected $plugin;
	public function __construct(Plugin $main, Main $selfMain){
		$this->plugin = $main;
		$this->initializeXEconEntity($this->getFolderByName(strtolower($this->getName())), $selfMain);
	}
	public function sendMessage($msg){
		$this->plugin->getLogger()->info(TextFormat::GRAY."[".$this->getName()."] $msg");
	}
	public function initDefaultAccounts(){
		$this->addAccount("Service", PHP_INT_MAX);
	}
	public function getAbsolutePrefix(){
		return "server_service_ent>>";
	}
}
