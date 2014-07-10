<?php

namespace xecon\tax\taxes;

use pocketmine\Player;
use xecon\entity\Entity;

class BaseTax{
	protected function compile($args){

	}
	public function execute(Player $player, Entity $entity){
		// TODO collect tax
		// TODO exemptions
	}
}
