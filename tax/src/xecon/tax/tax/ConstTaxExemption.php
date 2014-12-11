<?php

namespace xecon\tax\tax;

use pocketmine\Player;
use xecon\entity\PlayerEnt;
use xecon\tax\TaxPlugin;

class ConstTaxExemption extends TaxExemption{
	private $plugin;
	private $const;
	public function __construct(TaxPlugin $plugin, $const){
		$this->plugin = $plugin;
		$this->const = $const;
	}
	public function isExempted(Player $player, PlayerEnt $ent){
		return $this->const;
	}
}
