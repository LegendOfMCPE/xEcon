<?php

namespace xecon\commands;

use pocketmine\command\CommandSender;

class SetMoneySubcommand extends Subcommand{
	public function getName(){
		return "setmoney";
	}
	public function getDescription(){
		return "Set an economic entity's money";
	}
	public function getUsage(){
		return "<id> <account> <name>";
	}
	protected function onRun(CommandSender $sender, array $args){

	}
}
