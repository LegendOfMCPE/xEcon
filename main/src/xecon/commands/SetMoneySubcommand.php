<?php

namespace xecon\commands;

use pocketmine\command\CommandSender;

class SetMoneySubcommand extends Subcommand{
	public function getName(){
		return "set";
	}
	public function getDescription(){
		return "Set an economic entity's money";
	}
	public function getUsage(){
		return "<type> <name> <account> <amount>";
	}
	protected function onRun(CommandSender $sender, array $args){

	}
}
