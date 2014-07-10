<?php

namespace xecon\tax\taxes;

use pocketmine\Player;
use xecon\account\Account;
use xecon\entity\Entity;

class BaseTax extends Tax{
	/** @var string */
	protected $account;
	/** @var number */
	protected $amount;
	protected $hasWhite = false, $hasExempt = false;
	/** @var Expression[] */
	protected $whitelist = [], $exemptions = [];
	protected function compile($args){
		$this->account = $args["account"];
		$this->amount = $args["amount"];
		if(isset($args["name"])){
			$this->name = $args["name"];
		}
		if(isset($args["whitelist"])){
			$this->hasWhite = true;
			foreach($args["whitelist"] as $white){
				$this->whitelist[] = $this->compileExpression($white);
			}
		}
		elseif(isset($args["exemptions"])){
			$this->hasExempt = true;
		}
	}
	public function execute(Player $player, Entity $entity){
		if($this->hasWhite){
			foreach($this->whitelist as $white){
				if(!$white->check($player, $entity)){
					return;
				}
			}
		}
		elseif($this->hasExempt){
			foreach($this->exemptions as $ex){
				if($ex->check($player, $entity)){
					return;
				}
			}
		}
		$account = $entity->getAccount($this->account);
		if(!($account instanceof Account)){
			$this->getPlugin()->getLogger()->warning($player->getName()." doesn't have account {$this->account}. His/her $this will not be collected.");
			return;
		}
		// TODO take money
	}
	/**
	 * @param string $exp
	 * @return Expression
	 */
	protected function compileExpression($exp){
		return new Expression($exp);
	}
}
