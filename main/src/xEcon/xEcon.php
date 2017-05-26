<?php

namespace xEcon;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use spoondetector\SpoonDetector;
use xEcon\database\Database;
use xEcon\database\mysql\MysqlDatabase;
use xEcon\database\sqlite\SqliteDatabase;

class xEcon extends PluginBase implements Listener{
	private static $NAME = "xEcon";

	/** @var Database */
	private $db;

	/** @var AccountOwnerCache */
	private $accountOwnerCache;

	public function onLoad(){
		self::$NAME = $this->getName();
	}

	public function onEnable(){
		SpoonDetector::printSpoon($this, 'spoon.txt');
		$this->saveDefaultConfig();
		$this->initDb();
		$this->accountOwnerCache = new AccountOwnerCache($this);
	}

	private function initDb(){
		$type = strtolower($this->getConfig()->getNested("database.type", "sqlite"));
		if($type === "sqlite"){
			$this->db = new SqliteDatabase($this);
		}elseif($type === "mysql"){
			$this->db = new MysqlDatabase($this);
		}else{
			$this->getLogger()->warning("Unknown database type $type, using default value 'sqlite'");
			$this->db = new SqliteDatabase($this);
		}
	}

	public function getDatabase() : Database{
		return $this->db;
	}

	public function getAccountOwnerCache() : AccountOwnerCache{
		return $this->accountOwnerCache;
	}

	public function onDisable(){
		$this->db->close();
	}

	public static function validate($condition, $message = ""){
		if(!$condition){
			throw new \AssertionError($message);
		}
	}
}
