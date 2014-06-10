<?php

namespace xecon\commands;

use pocketmine\command\CommandSender;

class SetPlayerMoneySubcommand extends Subcommand{
	public function getName(){
		return "setpm";
	}
	public function getDescription(){
		return "Set a player's money";
	}
	public function getUsage(){
		return "<player> <account> <amount>";
	}
	public function onRun(CommandSender $sender, array $args){

	}
} 