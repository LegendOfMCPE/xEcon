<?php

namespace xecon\entity;

use pocketmine\Player;

class PlayerEnt extends Entity{
	public function __construct(Player $player){
		parent::__construct($this->getFolderByName($player->getName()), null);
	}
	public function getName(){
		return "PlayerEntity";
	}
	public function getAbsolutePrefix(){
		return "XECON_PLAYER_ENT";
	}
	public function getClass(){
		return "xecon\\entity\\PlayerEnt";
	}
}
