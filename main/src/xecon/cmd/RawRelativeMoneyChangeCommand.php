<?php

namespace xecon\cmd;

use pocketmine\command\CommandSender;
use xecon\account\Account;
use xecon\entity\Service;
use xecon\XEcon;

class RawRelativeMoneyChangeCommand extends XEconCommand{
	private $add;
	public function __construct(XEcon $main, $add){
		$this->add = $add;
		parent::__construct($main);
	}
	public function getName_(){
		return $this->add ? "rawadd":"rawtake";
	}
	public function getDesc_(){
		return ($this->add ? "Add money to":"Take money from") . " an account using its " .
		"raw name (type, name and account name)";
	}
	public function getUsage_(){
		return "/{$this->getName_()} <type> <name> <account> <amount> [details ...]";
	}
	public function execute_(CommandSender $sender, array $args){
		if(!isset($args[3])){
			return false;
		}
		$type = array_shift($args);
		$name = array_shift($args);
		$account = array_shift($args);
		$amount = array_shift($args);
		if(!is_numeric($amount)){
			return false;
		}
		$amount = floatval($amount);
		$ent = $this->getPlugin()->getEntity("$type/$name");
		if($ent === false){
			return "Entity $type/$name doesn't exist!";
		}
		$acc = $ent->getAccount($account);
		if(!($acc instanceof Account)){
			return "$type/$name doesn't have the account $account!";
		}
		$service = $this->getPlugin()->getService()->getService(Service::ACCOUNT_OPS);
		if($this->add){
			$service->pay($acc, $amount, implode(" ", $args));
			return "\$$amount have been given to $type/$name/$account.";
		}
		return $acc->pay($service, $amount, implode(" ", $args)) ?
			"\$$amount have been taken from $type/$name/$account.":
			"$type/$name/$account doesn't have enough money (\$$amount) to carry out this transaction!";
	}
}
