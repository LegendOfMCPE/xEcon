<?php

namespace xecon\cmd;

use pocketmine\Player;
use xecon\entity\PlayerEnt;
use xecon\XEcon;

class AddLoanCommand extends PlayerXEconCommand{
	public function __construct(XEcon $main){
		parent::__construct($main);
	}
	public function getName_(){
		return "addloan";
	}
	public function getDesc_(){
		return "Borrow new loan";
	}
	public function getUsage_(){
		return "/addloan"; // TODO
	}
	public function onRun(PlayerEnt $ent, Player $player, array $args){
		// TODO
	}
}
