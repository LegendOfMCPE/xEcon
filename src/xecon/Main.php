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
use xecon\commands\MyCommandMap;
use xecon\commands\SetMoneySubcommand;
use xecon\entity\Entity;

class Main extends PluginBase implements Listener{
	/** @var string directory where economic entity information is stored */
	private $edir;
	/** @var Session[] $sessions */
	private $sessions = [];
	/** @var Config */
	private $userConfig;
	/** @var MyCommandMap */
	private $subcommandMap;
	/** @var \SQLite3 */
	private $logs;
	public function onEnable(){
		$this->mkdirs();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->userConfig = new Config($this->getDataFolder()."config.yml", Config::YAML, [ // If I shifted my right hand one key leftwards on the QWERTY keyboard, YAML would become TANK
			"player-default-bank-money" => 500,
			"player-bank-max-money" => 100000,
			"player-default-cash-money" => 100,
			"player-max-cash-money" => 1000,
		]);
		$this->logs = new \SQLite3($this->getDataFolder().":memory:");
		$this->logs->exec("CREATE TABLE IF NOT EXISTS transactions (fromtype TEXT, fromname TEXT, fromaccount TEXT, totype TEXT, toname TEXT, toaccount TEXT, amount INT, details TEXT, tmstmp INT);");
		$this->subcommandMap = new MyCommandMap($this);
		$this->subcommandMap->register(new SetMoneySubcommand($this));
	}
	public function onDisable(){
		$this->logs->close();
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
	public function getSession(Player $player){
		return $this->sessions[$player->getID()];
	}
	public function getPlayerEntity(Player $player){
		return $this->sessions[$this->CID($player)]->getEntity();
	}
	public function getLogs(){
		return $this->logs;
	}
	public function logTransaction(Account $from, Account $to, $amount, $details = "None"){
		$op = $this->logs->prepare("INSERT INTO transactions (fromtype, fromname, fromaccount, totype, toname, toaccount, amount, details, tmstmp) WHERE (:fromtype, :fromname, :fromaccount, :totype, :toname, :toaccount, :amount, :details, :tmstmp);");
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
	public function getTransactionsToInTime(Entity $ent, $start, $end = null){
		if($end === null){
			$end = time();
		}
		$op = $this->logs->prepare("SELECT * FROM transactions WHERE totype = :totype AND toname = :toname AND (tmstmp BETWEEN :lowlim and :uplim);");
		$op->bindValue(":totype", $ent->getAbsolutePrefix());
		$op->bindValue(":toname", $ent->getName());
		$op->bindValue(":lowlim", $start);
		$op->bindValue(":uplim", $end);
		return $op->execute();
	}
	public function getTransactions($fromType = null, $fromName = null, $fromAccount = null, $toType = null, $toName = null, $toAccount = null, $tmstmpMin = 0, $tmstmpMax = null, $amountMin = 0, $amountMax = PHP_INT_MAX){ // is it possible to use RegExp to filter texts in SQLite3?
		$query = "SELECT * FROM transactions WHERE (tmstmp BETWEEN :timemin AND :timemax) AND (amount BETWEEN :amountmin AND :amountmax);";
		if(is_string($fromType)) $query .= " AND fromtype = :fromtype";
		if(is_string($fromName)) $query .= " AND fromname = :fromname";
		if(is_string($fromAccount)) $query .= " AND fromaccount = :fromaccount";
		if(is_string($toType)) $query .= " AND totype = :totype";
		if(is_string($toName)) $query .= " AND toname = :toname";
		if(is_string($toAccount)) $query .= " AND toaccount = :toaccount";
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
	public static function CID(Player $player){
		return $player->getID();
	}
}
