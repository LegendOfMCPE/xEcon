<?php

namespace xecon;

use xecon\entity\PlayerEnt;

class Session{
	/** @var PlayerEnt */
	private $ent;
	public function __construct($player, Main $main){
		$this->ent = $main->getPlayerEnt($player);
	}
	public function onQuit(){
		$this->ent->onQuit();
	}
	public function getEntity(){
		return $this->ent;
	}
}
