<?php

namespace xecon\tax\tax;

use pocketmine\Player;
use xecon\entity\PlayerEnt;
use xecon\tax\TaxPlugin;

class SingleTaxExemption extends TaxExemption{
	private $plugin;
	private $cmd, $args;
	public function __construct(TaxPlugin $plugin, $exp){
		$this->plugin = $plugin;
		$exp = trim($exp);
		$pos = strpos($exp, " ");
		$this->cmd = substr($exp, 0, $pos);
		$this->args = trim(substr($exp, $pos + 1));
	}
	public function isExempted(Player $player, PlayerEnt $ent){
		switch(strtolower($this->cmd)){
			case "isop":
				return $player->isOp();
			case "notop":
				return !$player->isOp();
			case "hasperm":
				return $player->hasPermission($this->args);
			case "noperm":
				return $player->hasPermission($this->args);
			case "compare":
				list($a, $oper, $b) = explode(" ", $this->args, 3);
				/** @noinspection PhpUnusedLocalVariableInspection */
				$a = $this->getValue($a);
				/** @noinspection PhpUnusedLocalVariableInspection */
				$b = $this->getValue($b);
				/** @var bool $result */
				eval("\$result = \$a $oper \$b;");
				return $result;
		}
		return false;
	}
	public function getValue($exp){
		return $this->plugin->getCommandValueManager()->getValue($exp);
	}
}
