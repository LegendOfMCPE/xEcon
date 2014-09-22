<?php

namespace xecon\cmd;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\Main;

class SetMoneyCommand extends XEconCommand{
	private $tarAccName;
	private $accGenName;
	/**
	 * @param Main $main
	 * @param string $tarAccName
	 * @param string $accGenName
	 */
	public function __construct(Main $main, $tarAccName, $accGenName){
		parent::__construct($main);
		$this->tarAccName = $tarAccName;
		$this->accGenName = $accGenName;
	}
	public function getName_(){
		return "set".$this->getAccountGenericName();
	}
	public function getDesc_(){
		return "Set the player's {$this->getAccountGenericName()} to a specified amount";
	}
	public function getUsage_(){
		return "/{$this->getName_()} <player> <amount> [.e] [details ...](add '.e' when you want to refer to offline players)";
	}
	public function getAliases_(){
		return $this->accGenName === "cash" ? ["set$"]:[];
	}
	public function execute_(CommandSender $sender, array $args){
		if(!isset($args[1])){
			return false;
		}
		$amount = $args[1];
		if(!is_numeric($amount)){
			return false;
		}
		$amount = (int) $amount;
		$e = false;
		if(isset($args[2]) and $args[2] === ".e"){
			$e = true;
		}
		if(!$e){
			$p = $this->getPlugin()->getServer()->getPlayer($args[0]);
			if(!($p instanceof Player)){
				return "Player $args[0] is not online! Try adding 'e' at the end of the command if he is offline.";
			}
			$ent = $this->getPlugin()->getPlayerEnt($p->getName());
		}else{
			$ent = $this->getPlugin()->getPlayerEnt($args[0], false);
			if(!($ent instanceof PlayerEnt)){
				return "Player $args[0] has not been registered!";
			}
		}
		$acc = $ent->getAccount($this->getTargetAccountName());
		$src = $this->getPlugin()->getService()->getService(Service::ACCOUNT_OPS);
		$details = implode(" ", array_slice($args, 2 + ($e ? 1:0)));
		$acc->transactWithAccountTo($amount, $src, strlen(trim($details)) > 0 ? trim($details):"{$this->getAccountGenericName()} set by a command");
		return true;
	}
	/**
	 * @return string
	 */
	protected function getTargetAccountName(){
		return $this->tarAccName;
	}
	/**
	 * @return string
	 */
	protected function getAccountGenericName(){
		return $this->accGenName;
	}
}
