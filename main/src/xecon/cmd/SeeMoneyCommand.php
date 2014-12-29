<?php

namespace xecon\cmd;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use xecon\account\Account;
use xecon\entity\PlayerEnt;

class SeeMoneyCommand extends XEconCommand{
	protected function getName_(){
		return "seemoney";
	}
	protected function getDesc_(){
		return "See account balances or yourself or others";
	}
	protected function getUsage_(){
		return "/seemoney [account=Cash] [player={yourself}] [type=Player]";
	}
	protected function getAliases_(){
		return "see\$";
	}
	public function execute_(CommandSender $sender, array $args){
		if(!isset($args[1]) and !($sender instanceof Player)){
			return "Usage for non-players: /seemoney <account> <player>";
		}
		$type = PlayerEnt::ABSOLUTE_PREFIX;
		$name = $sender->getName();
		$account = "Cash";
		if(isset($args[0])){
			$account = $args[0];
		}
		if(isset($args[1])){
			$name = $args[1];
		}
		if(isset($args[2])){
			$type = $args[2];
		}
		$permission = -1;
		if($sender->hasPermission("xecon.cmd.see.all")){
			$permission = 3;
		}
		elseif($sender->hasPermission("xecon.cmd.see.players")){
			$permission = 2;
		}
		elseif($sender->hasPermission("xecon.cmd.see.self")){
			$permission = 1;
		}
		elseif($sender->hasPermission("xecon.cmd.see.cash")){
			$permission = 0;
		}
		if($permission < 0){
			return "You don't have permission to use this command.";
		}
		if($permission < 1 and strtolower($account) !== "cash"){
			return "You don't have permission to check balance of account $account.";
		}
		if($permission < 2 and strtolower($name) !== strtolower($sender->getName())){
			return "You don't have permission to check balance of other players.";
		}
		if($permission < 3 and strtolower($type) !== strtolower(PlayerEnt::ABSOLUTE_PREFIX)){
			return "You don't have permission to check balance of non-players.";
		}
		$ent = $this->getPlugin()->getEntity($un = "$type/$name");
		if($ent === false){
			return "$un doesn't exist!";
		}
		$acc = $ent->getAccount($account);
		if(!($acc instanceof Account)){
			return "$un doesn't have an account called $acc!";
		}
		return "Balance of $acc: \${$acc->getAmount()}";
	}
	public function testPermissionSilent(CommandSender $sender){
		return $sender->hasPermission("xecon.cmd.see");
	}
}
