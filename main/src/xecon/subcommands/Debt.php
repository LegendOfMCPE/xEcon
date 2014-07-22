<?php

namespace xecon\subcommands;

use pocketmine\Player;
use xecon\entity\Entity;

class Debt extends PlayerSubcommand{
	public function getName(){
		return "debt";
	}
	public function getDescription(){
		return "Manage loans";
	}
	public function getAliases(){
		return ["loan"];
	}
	public function getUsage(){
		return "<take|return|balance>";
	}
	protected function onRun(Player $player, Entity $entity, array $args){
		// TODO
	}
}
