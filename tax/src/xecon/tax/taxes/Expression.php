<?php

namespace xecon\tax\taxes;

use pocketmine\Player;
use xecon\account\Account;
use xecon\entity\Entity;
use xecon\tax\Main;

class Expression{
	// TODO make this compile on construction to make it faster
	private $src;
	private $exp;
//	private $strings = [];
	/** @var Expression[] */
	private $subexp = [];
	/** @var Main */
	private $main;
	/** @var Expression|null */
	private $parent;
	public function __construct(Main $main, $exp, Expression $parent = null){
		$this->src = $exp;
		$this->main = $main;
		$this->parent = $parent;
//		$exp = preg_replace_callback("#([^\\\\])'(.*[^\\\\])'#", function($match){
//			$id = count($this->strings);
//			$this->strings[$id] = $match[2];
//			return $match[1]."STRLIT_$id";
//		}, $exp);
		$exp = preg_replace_callback("# (and|or) #i", function($match){
			switch(strtolower($match[1])){
				case "and":
					return "&";
				case "or":
					return "|";
//				case "xor":
//					return "^";
			}
			throw new \RuntimeException("RegExp error in ".get_class()." boolean evaluator");
		}, $exp);
		$exp = preg_replace_callback("#\\((.*)\\)#", function($match){
			// http://en.wikipedia.org/wiki/Regular_expression#Lazy_quantification (greedy)
			$id = count($this->subexp);
			$this->subexp[$id] = new Expression($this->main, $match[2]); // the recursive function
			return $match[1]."SUBEXP_$id".$match[3];
		}, $exp);
		$this->exp = $exp;
	}
	/**
	 * @param Player $player
	 * @param Entity $entity
	 * @return mixed
	 */
	public function check(Player $player, Entity $entity){
		switch(strtolower($this->exp)){
			case "op":
				return $player->isOp();
			case "notop":
				return !$player->isOp();
		}
		if(count($tokens = explode("_", $this->exp)) === 2){
			switch(strtoupper($tokens[0])){
				case "ACCOUNT":
					$account = $entity->getAccount($tokens[1]);
					if(!($account instanceof Account)){
						$this->main->getLogger()->notice("Account $tokens[1] as specified in \"".
							$this->getGreatestGrandparent()->getSrc()."\" doesn't exist for ".$player->getName().
							"! \$0 will be assumed as the balance for account $tokens[1].");
						return 0;
					}
					return $account->getAmount();
				case "SUBEXP":
					return $this->subexp[(int) $tokens[1]]->check($player, $entity);
//				case "STRLIT":
//					return $this->strings[(int) $tokens[1]];
			}
		}
		if(strpos($this->exp, "|") !== false){
			return $this->subexp[0]->check($player, $entity) or $this->subexp[1]->check($player, $entity);
		}
		if(strpos($this->exp, "&") !== false){
			return $this->subexp[0]->check($player, $entity) and $this->subexp[1]->check($player, $entity);
		}
		if(strpos($this->exp, ">") !== false){
			return $this->subexp[0]->check($player, $entity) > $this->subexp[1]->check($player, $entity);
		}
		if(strpos($this->exp, "<") !== false){
			return $this->subexp[0]->check($player, $entity) < $this->subexp[1]->check($player, $entity);
		}
		if(strpos($this->exp, "=") !== false){
			return $this->subexp[0]->check($player, $entity) == $this->subexp[1]->check($player, $entity);
		}
		return floatval($this->exp);
	}
	public function getGreatestGrandparent(){
		if($this->parent instanceof Expression){
			return $this->parent->getGreatestGrandparent();
		}
		return $this;
	}
	public function getSrc(){
		return $this->src;
	}
}
