<?php

namespace xecon;

use pocketmine\Player;
use xecon\entity\PlayerEnt;

class Session{
	public function __construct(Player $player){
		$this->ent = new PlayerEnt($player);
	}
	public function onQuit(){
		
	}
}
