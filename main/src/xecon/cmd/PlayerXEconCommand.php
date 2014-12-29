<?php

namespace xecon\cmd;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use xecon\entity\PlayerEnt;

abstract class PlayerXEconCommand extends XEconCommand{
	public function execute_(CommandSender $sender, array $args){
		if(!($sender instanceof Player)){
			return "Please run this command in-game.";
		}
		$ent = $this->getPlugin()->getPlayerEnt($sender->getName(), false);
		return $this->onRun($ent, $sender, $args);
	}
	protected abstract function onRun(PlayerEnt $ent, Player $player, array $args);
}
