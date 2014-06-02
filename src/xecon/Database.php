<?php

namespace xecon;

use xecon\entity\Entity;

use pocketmine\Player;
use pocketmine\Server;

class Database{
	public function __construct(){
		$this->main = Main::get();
		$this->server = Server::getInstance();
	}
	public function init(Entity $entity){ // initialize
		$entity->getAbsName();
	}
	public function fine(Entity $entity){ // finalize
		
	}
}
