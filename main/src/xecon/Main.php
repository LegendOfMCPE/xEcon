<?php

namespace xecon;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use xecon\account\Account;
use xecon\entity\Entity;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\log\LogProvider;
use xecon\log\Transaction;
use xecon\provider\JSONDataProvider;
use xecon\provider\MysqliDataProvider;
use xecon\provider\SQLite3DataProvider;
use xecon\subcommands\Subcommand;
use xecon\utils\CallbackPluginTask;

class Main extends PluginBase implements Listener{
	/** @var Session[] $sessions */
	private $sessions = [];
	/** @var log\LogProvider */
	private $log;
	/** @var Service */
	private $service;
	/** @var Subcommand[] */
	private $subcommands = [];
	/** @var \WeakRef[] */
	private $ents = [];
	/** @var \xecon\provider\DataProvider */
	private $dataProvider;
	/** @var \mysqli|null */
	private $universalMysqli = null;
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$data = $this->getConfig()->get("data provider");
		switch($name = strtolower($data["name"])){
			case "sqlite3":
				$this->dataProvider = new SQLite3DataProvider($this, $data[$name]);
				break;
			case "disk":
				$this->dataProvider = new JSONDataProvider($this, $data[$name]);
				break;
			case "mysqli":
				if($data[$name]["use universal"]){
					$db = $this->getUniversalMysqliDatabase($this);
					if($db === null){
						return;
					}
				}
				else{
					$details = $data[$name]["connection details"];
					$db = new \mysqli($details["host"], $details["username"], $details["password"], $details["database"], $details["port"]);
					if($db->connect_error){
						$this->getLogger()->critical("Unable to connect to core MySQL database! Reason: ".$db->connect_error);
						$this->getLogger()->critical("Disabling due to required core MySQL database not connectable.");
						$this->getLogger()->critical("Try changing the data provider type or fixing the connection details.");
						$this->getPluginLoader()->disablePlugin($this);
					}
				}
				$this->dataProvider = new MysqliDataProvider($this, $db, $data[$name]);
		}
		$this->logs = new \SQLite3($this->getDataFolder()."logs.sq3");
		$this->logs->exec("CREATE TABLE IF NOT EXISTS transactions (
				fromtype TEXT,
				fromname TEXT,
				fromaccount TEXT,
				totype TEXT,
				toname TEXT,
				toaccount TEXT,
				amount REAL,
				details TEXT,
				tmstmp INTEGER)");
		$this->service = new Service($this);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CallbackPluginTask($this, array($this, "collectGarbage")), 200, 200);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CallbackPluginTask($this, array($this, "opQueue"), [], array($this, "opQueue")), 1, 1);
	}
	public function onDisable(){
		$this->logs->close();
		$this->dataProvider->close();
		if($this->universalMysqli instanceof \mysqli){
			$this->universalMysqli->close(); // disable dependencies too
		}
	}
	public function registerSubcommand(Subcommand $subcommand){
		$this->subcommands[$subcommand->getName()] = $subcommand;
		foreach($subcommand->getAliases() as $alias){
			$this->subcommands[$alias] = $subcommand;
		}
	}
	public function onCommand(CommandSender $sender, Command $command, $alias, array $args){
		$sub = trim(strtolower(array_shift($args)));
		if(isset($this->subcommands[$sub])){
			$this->subcommands[$sub]->execute($sender, $args);
			return true;
		}
		elseif($sub === "help"){
			// TODO
			return true;
		}
		else{
			return false;
		}
	}
	public function getUniversalMysqliDatabase(Plugin $ctx, $disableOnFailure = true){
		if(!($this->universalMysqli instanceof \mysqli)){
			$data = $this->getConfig()->get("universal mysqli database")["connection details"];
			$this->universalMysqli = new \mysqli($data["host"], $data["username"], $data["password"], $data["database"], $data["port"]);
			if($this->universalMysqli->connect_error){
				$ctx->getLogger()->critical("Failed to connect to the xEcon universal MySQL database! Reason: ".$this->universalMysqli->connect_error);
				if($disableOnFailure){
					if($ctx !== $this){
						$desc = $ctx->getDescription();
						$this->getLogger()->critical("Disabling ".$desc->getFullName()." by ".implode(", ", $desc->getAuthors())." because the required universal MySQL database cannot be connected to.");
					}
					else{
						$this->getLogger()->critical("Disabling due to required universal MySQL database not connectable.");
					}
					$ctx->getPluginLoader()->disablePlugin($ctx);
				}
				$this->universalMysqli = null;
			}
		}
		return $this->universalMysqli;
	}
	public function getMaxBankOverdraft(){
		return $this->getConfig()->get("player account")["bank"]["overdraft"];
	}
	public function getDefaultBankMoney(){
		return $this->getConfig()->get("player accont")["default"]["bank"];
	}
	public function getDefaultCashMoney(){
		return $this->getConfig()->get("player accont")["default"]["cash"];
	}
	public function getMaxBankMoney(){
		return $this->getConfig()->get("player accont")["max"]["bank"];
	}
	public function getMaxCashMoney(){
		return $this->getConfig()->get("player accont")["max"]["cash"];
	}
	public function isGiveForEachName(){
		return $this->getConfig()->get("player accont")["default"]["give for each ip"];
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
	public function getSessions(){
		return $this->sessions;
	}
	public function getSession(Player $player){
		return $this->sessions[$player->getID()];
	}
	public function getLogs(){
		return $this->logs;
	}
	public function getService(){
		return $this->service;
	}
	public function logTransaction(Account $from, Account $to, $amount, $details = "None"){
		$this->log->logTransaction(new Transaction($from, $to, $amount, $details));
	}
	/**
	 * @param string|null $fromType
	 * @param string|null $fromName
	 * @param string|null $fromAccount
	 * @param string|null $toType
	 * @param string|null $toName
	 * @param string|null $toAccount
	 * @param int $tmstmpMin
	 * @param int|null $tmstmpMax
	 * @param int $amountMin
	 * @param int $amountMax
	 * @param int|string $fromToOper
	 * @return Transaction[]
	 */
	public function getTransactions($fromType = null, $fromName = null, $fromAccount = null, $toType = null, $toName = null, $toAccount = null, $tmstmpMin = 0, $tmstmpMax = null, $amountMin = 0, $amountMax = PHP_INT_MAX, $fromToOper = LogProvider::O_OR){ // is it possible to use RegExp to filter texts in SQLite3?
		return $this->log->getTransactions($fromType, $fromName, $fromAccount, $toType, $toName, $toAccount, $amountMin, $amountMax, $tmstmpMin, $tmstmpMax, $fromToOper);
	}
	public function getDataProvider(){
		return $this->dataProvider;
	}
	/**
	 * @param $name
	 * @return PlayerEnt
	 */
	public function getPlayerEnt($name){
		$this->collectGarbage();
		if($name instanceof Player){
			$name = $name->getName();
		}
		$name = strtolower($name);
		$realName = $name;
		$name = PlayerEnt::ABSOLUTE_PREFIX."/$name";
		if(!isset($this->ents[$name])){
			new PlayerEnt($realName, $this); // It will automatically register to addEntity() in the constructor
		}
		return $this->ents[$name]->get();
	}
	public function addEntity(Entity $entity){
		$this->ents[$entity->getUniqueName()] = new \WeakRef($entity);
	}
	public function getEntity($uniqueName){
		return isset($this->ents[$uniqueName]) ? $this->ents[$uniqueName]->get():false;
	}
	public function collectGarbage(){
		foreach($this->ents as $offset => $ent){
			if(!$ent->valid()){
				unset($this->ents[$offset]);
			}
		}
	}
	public static function CID(Player $player){
		return $player->getID();
	}
}
