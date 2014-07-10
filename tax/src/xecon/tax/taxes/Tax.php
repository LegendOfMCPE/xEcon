<?php

namespace xecon\tax\taxes;


use pocketmine\Player;
use pocketmine\plugin\Plugin;
use xecon\entity\Entity;

abstract class Tax{
	protected $args;
	protected $name;
	public function __construct(array $args, Plugin $main){
		$this->compile($args);
		$this->main = $main;
		$simpleClassName = array_slice(explode("\\", get_class($this)), -1)[0];
		$this->name = strtolower(substr($simpleClassName, 0, 1)).preg_replace_callback("#[A-Z]#", function($match){
			return strtolower($match[0]);
		}, substr($simpleClassName, 1));
	}
	protected function compile($args){
		$this->args = $args;
	}
	public abstract function execute(Player $player, Entity $entity);
	public function getPlugin(){
		return $this->main;
	}
	public function __toString(){
		return $this->name;
	}
}
