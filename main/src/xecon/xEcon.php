<?php

namespace xecon;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use spoondetector\SpoonDetector;
use xecon\account\AccountOwner;
use xecon\account\AccountOwnerAdapter;
use xecon\database\Database;
use xecon\utils\AccountOwnerCache;

class xEcon extends PluginBase implements Listener{
	private static $NAME = "xEcon";

	/** @var AccountOwnerCache */
	private $ownerCache;
	/** @var Database */
	private $db;

	public function onLoad(){
		self::$NAME = $this->getName();
	}

	public function onEnable(){
		SpoonDetector::printSpoon($this, 'spoon.txt');
		$this->saveDefaultConfig();
		$this->ownerCache = new AccountOwnerCache($this);
		$this->initDb();
	}

	private function initDb(){
		// TODO implement
	}

	public function getDataBase() : Database{
		return $this->db;
	}

	public function getAccountOwnerCache() : AccountOwnerCache{
		return $this->ownerCache;
	}

	public function onDisable(){
		if(isset($this->db)){
			$this->db->close();
		}
	}

	public function loadOrGetOwner(string $type, string $name, callable $ownerAcceptor, AccountOwnerAdapter $adapter = null) : bool{
		$owner = $this->getAccountOwnerCache()->fetch($type, $name);
		if($owner !== null){
			$ownerAcceptor($owner);
			return true;
		}else{
			AccountOwner::load($this, $type, $name, $ownerAcceptor, function(\Exception $e) use ($type, $name){
				$this->getLogger()->error("Failed loading account data for $type:$name!");
				$this->getLogger()->logException($e);
			}, $adapter);
			return false;
		}
	}

	public static function validate($condition, $message = ""){
		if(!$condition){
			throw new \AssertionError($message);
		}
	}
}
