<?php

namespace xecon\subcommands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use xecon\entity\Entity;

abstract class PlayerSubcommand extends Subcommand{
	protected function run(CommandSender $sender, array $args){
		if(!($sender instanceof Player)){
			return "Please run this command in-game.";
		}
		$entity = $this->getMain()->getPlayerEnt($sender);
		return $this->onRun($sender, $entity, $args);
	}
	protected abstract function onRun(Player $player, Entity $entity, array $args);
}
