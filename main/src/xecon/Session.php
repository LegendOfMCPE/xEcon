<?php

namespace xecon;

use pocketmine\Player;
use xecon\entity\PlayerEnt;

class Session{
	/** @var PlayerEnt */
	private $ent;
	public function __construct(Player $player, Main $main){
		$this->ent = new PlayerEnt($player, $main);
	}
	public function onQuit(){
		$this->ent->onQuit();
	}
	public function getEntity(){
		return $this->ent;
	}
}
