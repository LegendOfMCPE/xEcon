<?php

namespace xecon\subcommands;

use pocketmine\Player;
use xecon\account\Loan;
use xecon\entity\Entity;

class Debt extends PlayerSubcommand{
	public function getName(){
		return "debt";
	}
	public function getDescription(){
		return "Manage loans";
	}
	public function getAliases(){
		return ["loan"];
	}
	public function getUsage(){
		return "<take|return|balance>";
	}
	protected function onRun(Player $player, Entity $entity, array $args){
		if(!isset($args[0])){
			return self::WRONG_USE;
		}
		switch($sub = array_shift($args)){
			case "take":
				if(!isset($args[0])){
					return "Usage: /x debt take <amount>";
				}
				$config = $this->getMain()->getConfig();
				$loanData = $config->get("loans");
				$amount = $args[0];
				$due = $loanData["due"] * 3600 + time();
				$interest = $loanData["interest"];
				$entity->addLoan($this->getMain()->getService()->getService("BankLoanSource"), $amount, $due, $interest);
				return "\$$amount of loan has been taken.";
			case "view":
				$output = "Your loans: (current time is ".date("M j, y H:i").")\n";
				$list = [
					"amount" => ["amount (\$)"],
					"create" => ["borrowed at"],
					"due" => ["due at"],
					"interest" => ["interest (%+/h)"],
					"total" => ["total (\$)"],
				];
				$cnt = 1;
				foreach($entity->getLoans() as $loan){
					$list["amount"][] = (string) $loan->getOriginalAmount();
					$list["create"][] = date("M j, y H:i", $loan->getCreation());
					$list["due"][] = date("M j, y H:i", $loan->getDue());
					$list["interest"][] = $loan->getIncreasePerHour();
					$list["total"][] = $loan->getAmount();
					$cnt++;
				}
				$clist = $list;
				foreach($list as $col => $data){
					$maxLen = max(array_map("strlen", $data));
					foreach($data as $key => $str){
						$clist[$col][$key] = $str.str_repeat(" ", $maxLen - strlen($str));
					}
				}
				for($i = 0; $i < $cnt; $i++){
					$output .= implode(" | ", [
						$clist["amount"][$i],
						$clist["create"][$i],
						$clist["due"][$i],
						$clist["interest"][$i],
						$clist["total"][$i]
					]);
					$output .= "\n";
				}
				return $output;
		}
		return self::WRONG_USE;
	}
}
