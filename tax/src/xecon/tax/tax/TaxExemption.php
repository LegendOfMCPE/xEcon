<?php

namespace xecon\tax\tax;

use pocketmine\Player;
use xecon\entity\PlayerEnt;
use xecon\tax\TaxPlugin;

class TaxExemption{
	private $plugin;
	/** @var TaxExemption */
	private $front, $back;
	private $isAnd;
	public function __construct(TaxPlugin $plugin, $exp){
		$this->plugin = $plugin;
		$exp = trim($exp);
		$bracketCnt = 0;
		$state = 0; // expecting (
		$tmp = "";
		for($i = 0; $i < strlen($exp); $i++){
			$char = substr($exp, $i, 1);
			if($state === 0){
				if($char !== "("){
					throw new \RuntimeException("Unexpected '$char', expecting '(' after character $i");
				}
				$state = 1; // expecting statement or )
				$tmp = "";
			}
			elseif($state === 1){
				if($char === ")"){
					$bracketCnt--;
					if($bracketCnt === 0){
						if(isset($this->front)){
							$this->back = new TaxExemption($plugin, $tmp);
							break;
						}
						if(strpos($tmp, "(") === false){
							$this->front = new SingleTaxExemption($plugin, $tmp);
						}
						else{
							$this->front = new TaxExemption($plugin, $tmp);
						}
						$tmp = "";
						$state = 2; // expecting logic
						continue;
					}
				}
				$tmp .= $char;
			}
			elseif($state === 2){
				if(strtolower(substr($exp, $i, 5)) === " AND "){
					$this->isAnd = true;
					$i += 4;
				}
				elseif(strtolower(substr($exp, $i, 4)) === " OR "){
					$this->isAnd = false;
					$i += 3;
				}
				else{
					throw new \RuntimeException("Expecting ' AND ' or ' OR ' after character $i");
				}
				$state = 0;
			}
		}
		if(!isset($this->back)){
			throw new \RuntimeException("Unexpected end of expression");
		}
	}
	public function isExempted(Player $player, PlayerEnt $ent){
		$front = $this->front->isExempted($player, $ent);
		$back = $this->back->isExempted($player, $ent);
		return $this->isAnd ? ($front and $back):($front or $back);
	}
}
