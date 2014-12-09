<?php

namespace xecon\cmd;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\XEcon;

class RelativeChangeMoneyCommand extends XEconCommand{
	/** @var bool */
	private $add;
	/** @var string */
	private $cmdName, $actionStr, $accName, $accHumanName;
	/** @var string[] */
	private $aliases_;
	public function __construct(XEcon $main, $name, $add, $actionStr, $accName, $accHumanName, array $aliases = []){
		$this->cmdName = $name;
		$this->add = $add;
		$this->actionStr = $actionStr;
		$this->accName = $accName;
		$this->accHumanName = $accHumanName;
		$this->aliases_ = $aliases;
		parent::__construct($main);
	}
	protected function getName_(){
		return $this->cmdName;
	}
	protected function getDesc_(){
		return ucfirst($this->actionStr) . " a player's {$this->accHumanName}";
	}
	protected function getUsage_(){
		return "/{$this->getName_()} <player> <amount> [.e] [details ...] (add '.e' if the player might be offline)";
	}
	protected function getAliases_(){
		return $this->aliases_;
	}
	public function execute_(CommandSender $sender, array $args){
		if(!isset($args[1])){
			return false;
		}
		$name = array_shift($args);
		$amount = array_shift($args);
		if(!is_numeric($amount)){
			return false;
		}
		$amount = floatval($amount);
		$e = false;
		if(isset($args[0]) and $args[0] === ".e"){
			$e = true;
			array_shift($args);
		}
		if($e){
			$ent = $this->getPlugin()->getPlayerEnt($name, false);
			if(!($ent instanceof PlayerEnt)){
				return "$name is not registered! If you are using a similar name, don't use '.e'.";
			}
		}
		else{
			$player = $this->getPlugin()->getServer()->getPlayer($name);
			if(!($player instanceof Player)){
				return "$name is not online! Try adding '.e' if the player is offline.";
			}
			$ent = $this->getPlugin()->getPlayerEnt($player->getName());
		}
		$acc = $ent->getAccount($this->accName);
		$service = $this->getPlugin()->getService()->getService(Service::ACCOUNT_OPS);
		if($this->add){
			$service->pay($acc, $amount, implode(" ", $args));
			return "\$$amount has been given to {$ent->getName()}.";
		}
		else{
			return $acc->pay($service, $amount, implode(" ", $args)) ?
				"\$$amount has been taken from {$ent->getName()}.":
				"{$ent->getName()} doesn't have so much {$this->accHumanName}";
		}
	}
}
