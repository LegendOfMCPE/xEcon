<?php

namespace xecon\tax\taxes;


use pocketmine\Player;
use xecon\entity\Entity;

abstract class Tax{
	protected $args;
	public function __construct(array $args){
		$this->compile($args);
	}
	protected function compile($args){
		$this->args = $args;
	}
	public abstract function execute(Player $player, Entity $entity);
}
