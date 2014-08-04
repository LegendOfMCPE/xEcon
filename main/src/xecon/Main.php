<?php

namespace xecon;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use xecon\account\Account;
use xecon\entity\Entity;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\subcommands\Subcommand;
use xecon\utils\CallbackPluginTask;

class Main extends PluginBase implements Listener{
	const QUEUE_LOG_GET = "GET";
	const QUEUE_LOG_LOG = "PUT";
	/** @var string directory where economic entity information is stored */
	private $edir;
	/** @var Session[] $sessions */
	private $sessions = [];
	/** @var \SQLite3 */
	private $logs;
	/** @var Service */
	private $service;
	/** @var Subcommand[] */
	private $subcommands = [];
	/** @var \WeakRef[] */
	private $ents = [];
	/** @var string[] */
	public static $queue = [];
	public static $results = [];
	/** @var Config */
	private $defaultedIPs;
	public function onEnable(){
		$this->mkdirs();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->defaultedIPs = new Config($this->getDataFolder()."default-given IPs.list", Config::ENUM);
		$this->logs = new \SQLite3($this->getDataFolder()."logs.sq3");
		$this->logs->exec("CREATE TABLE IF NOT EXISTS transactions (fromtype TEXT, fromname TEXT, fromaccount TEXT, totype TEXT, toname TEXT, toaccount TEXT, amount INT, details TEXT, tmstmp INT)");
		$this->service = new Service($this);
		// TODO I remember I wanted to do something here, but after chasing a few PocketMine-MP bugs, I forgot it. :( I made this mark here to remind ourselves that we should add something here. It is something about callback tasks.
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CallbackPluginTask($this, array($this, "collectGarbage")), 200, 200);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CallbackPluginTask($this, array($this, "opQueue"), [], array($this, "opQueue")), 1, 1);
	}
	public function onDisable(){
		$this->logs->close();
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
	public function opQueue(){
		while(self::$queue){
			/** @var array $request */
			$request = unserialize(array_shift(self::$queue));
			$id = array_shift($request);
			switch(array_shift($request)){
				case self::QUEUE_LOG_GET:
					/** @var \SQLite3Result $result */
					$result = call_user_func_array(array($this, "getTransactions"), $request);
					$data = [];
					while(($datum = $result->fetchArray(SQLITE3_ASSOC)) !== false){
						$data[] = $datum;
					}
					self::$results[$id] = serialize($data);
			}
		}
	}
	public function getDefaultBankMoney(){
		return $this->getConfig()->get("player-default-bank-money");
	}
	public function getDefaultCashMoney(){
		return $this->getConfig()->get("player-default-cash-money");
	}
	public function getMaxBankMoney(){
		return $this->getConfig()->get("player-max-bank-money");
	}
	public function getMaxCashMoney(){
		return $this->getConfig()->get("player-max-cash-money");
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
	public function touchIP(PlayerEnt $ent){
		$player = $ent->getPlayer();
		if(!($player instanceof Player)){
			throw new \BadMethodCallException("Main::touchIP() must be provided with a PlayerEnt instance with field \$player as a Player instance");
		}
		if(!($this->defaultedIPs->exists($player->getAddress()))){

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
		$op = $this->logs->prepare("INSERT INTO transactions (fromtype, fromname, fromaccount, totype, toname, toaccount, amount, details, tmstmp) VALUES (:fromtype, :fromname, :fromaccount, :totype, :toname, :toaccount, :amount, :details, :tmstmp)");
		$op->bindValue(":fromtype", $from->getEntity()->getAbsolutePrefix());
		$op->bindValue(":fromname", $from->getEntity()->getName());
		$op->bindValue(":fromaccount", $from->getName());
		$op->bindValue(":totype", $to->getEntity()->getAbsolutePrefix());
		$op->bindValue(":toname", $to->getEntity()->getName());
		$op->bindValue(":toaccount", $to->getName());
		$op->bindValue(":amount", $amount);
		$op->bindValue(":details", $details);
		$op->bindValue(":tmstmp", time());
		$op->execute();
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
	 * @return \SQLite3Result
	 */
	public function getTransactions($fromType = null, $fromName = null, $fromAccount = null, $toType = null, $toName = null, $toAccount = null, $tmstmpMin = 0, $tmstmpMax = null, $amountMin = 0, $amountMax = PHP_INT_MAX, $fromToOper = "OR"){ // is it possible to use RegExp to filter texts in SQLite3?
		if($fromToOper === T_LOGICAL_XOR or $fromToOper === T_XOR_EQUAL){
			$fromToOper = "XOR";
		}
		elseif($fromToOper === T_LOGICAL_OR or $fromToOper === T_OR_EQUAL or $fromToOper === T_BOOLEAN_OR){
			$fromToOper = "OR";
		}
		elseif($fromToOper === T_AND_EQUAL or $fromToOper === T_BOOLEAN_AND or $fromToOper === T_LOGICAL_AND){
			$fromToOper = "AND";
		}
		$query = "SELECT * FROM transactions WHERE (tmstmp BETWEEN :timemin AND :timemax) AND (amount BETWEEN :amountmin AND :amountmax) ORDER BY tmstmp ASC;";
		$from = "";
		if(is_string($fromType) or is_string($fromName) or is_string($fromAccount)){
			$from .= "(";
			$exprs = [];
			if(is_string($fromType)) $exprs[] = "fromtype = :fromtype";
			if(is_string($fromName)) $exprs[] = "fromname = :fromname";
			if(is_string($fromAccount)) $exprs[] = "fromaccount = :fromaccount";
			$from .= implode(" AND ", $exprs);
			$from .= ")";
		}
		$to = "";
		if(is_string($toType) or is_string($toName) or is_string($toAccount)){
			$to .= "(";
			$exprs = [];
			if(is_string($toType)) $exprs[] = "totype = :totype";
			if(is_string($toName)) $exprs[] = "toname = :toname";
			if(is_string($toAccount)) $exprs[] = "toaccount = :toaccount";
			$to .= implode(" AND ", $exprs);
			$to .= ")";
		}
		if($from and $to){
			$query .= " AND ($from $fromToOper $to)";
		}
		elseif($from or $to){
			if($from){
				$query .= " AND $from";
			}
			else{
				$query .= " AND $to";
			}
		}
		$op = $this->logs->prepare($query);
		$op->bindValue(":timemin", $tmstmpMin);
		$op->bindValue(":timemax", $tmstmpMax === null ? time():$tmstmpMax);
		$op->bindValue(":amountmin", $amountMin);
		$op->bindValue(":amountmax", $amountMax);
		if(strpos($query, ":fromtype") !== false){
			$op->bindValue(":fromtype", $fromType);
		}
		if(strpos($query, ":fromname") !== false){
			$op->bindValue(":fromname", $fromName);
		}
		if(strpos($query, ":fromaccount") !== false){
			$op->bindValue(":fromaccount", $fromAccount);
		}
		if(strpos($query, ":totype") !== false){
			$op->bindValue(":totype", $toType);
		}
		if(strpos($query, ":toname") !== false){
			$op->bindValue(":toname", $toName);
		}
		if(strpos($query, ":toaccount") !== false){
			$op->bindValue(":toaccount", $toAccount);
		}
		return $op->execute();
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
			$this->ents[$name] = new \WeakRef(new PlayerEnt($realName, $this));
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
