<?php

namespace xecon;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use spoondetector\SpoonDetector;
use xecon\utils\AccountOwnerCache;

class xEcon extends PluginBase implements Listener{
	private static $NAME = "xEcon";
	/** @var AccountOwnerCache */
	private $ownerCache;

	public function onLoad(){
		self::$NAME = $this->getName();
	}

	public function onEnable(){
		SpoonDetector::printSpoon($this, 'spoon.txt');
		$this->saveDefaultConfig();
		$this->ownerCache = new AccountOwnerCache($this);
	}

	public function getAccountOwnerCache():AccountOwnerCache{
		return $this->ownerCache;
	}

	public function onDisable(){

	}

	public static function validate($condition, $message = ""){
		if(!$condition){
			throw new \AssertionError($message);
		}
	}
}
