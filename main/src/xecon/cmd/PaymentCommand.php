<?php

namespace xecon\cmd;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use xecon\entity\PlayerEnt;

class PaymentCommand extends PlayerXEconCommand{
	protected function getName_(){
		return "pay";
	}
	protected function getDesc_(){
		return "Pay money to another player";
	}
	protected function getUsage_(){
		return "/pay <player> <amount> [-bank] [details...]";
	}
	public function onRun(PlayerEnt $entity, Player $player, array $args){
		if(!isset($args[1])){
			return false;
		}
		$bank = isset($args[2]) and strtolower($args[2]) === "-bank";
		if($bank and !$player->hasPermission("xecon.cmd.pay.bank")){
			return "You don't have permission to pay bank money to other players.";
		}
		elseif(!$bank and !$player->hasPermission("xecon.cmd.pay.cash")){
			return "You don't have permission to pay cash to other players.";
		}
		$toEnt = $this->getPlugin()->getPlayerEnt($args[0]);
		if(!($toEnt instanceof PlayerEnt)){
			return "Player $args[0] is not registered in the database!";
		}
		$to = $toEnt->getAccount($name = ($bank ? PlayerEnt::ACCOUNT_BANK:PlayerEnt::ACCOUNT_CASH));
		$from = $entity->getAccount($name);
		$amount = floatval($args[1]);
		if($amount <= 0){
			return "Amount must be larger than zero!";
		}
		if(!$from->canPay($amount)){
			return "You cannot pay \$$amount from your $from account.";
		}
		if(!$to->canReceive($amount)){
			return "$args[0]'s $to account cannot receive \$$amount.";
		}
		$from->pay($to, $amount, implode(" ", array_slice($args, 2 + ($bank ? 1:0))));
		return "Transaction completed: \$$amount has been paid to $args[1]'s $to account from your $from account.";
	}
	public function testPermissionSilent(CommandSender $sender){
		return $sender->hasPermission("xecon.cmd.pay.*");
	}
}
