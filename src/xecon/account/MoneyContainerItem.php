<?php

namespace xecon\account;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;

abstract class MoneyContainerItem extends Item{
	public function __construct($id, $meta = 0, $count = 1, $name = "Money container", $perAmount, $maxStack = 16, $throwable = true){
		parent::__construct($id, $meta, $count, $name);
		$this->perAmount = $perAmount;
		$this->isActivable = $throwable;
		$this->maxStackSize = $maxStack;
	}
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $z){
		if($this->isActivable){
			// TODO drop money
		}
	}
}
