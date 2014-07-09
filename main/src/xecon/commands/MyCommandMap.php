<?php

namespace xecon\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as Font;

class MyCommandMap{ // implements \pocketmine\command\CommandMap
	/**
	 * @var Subcommand[] $cmds
	 */
	protected $cmds;
	public function register(Subcommand $cmd){
		$this->cmds[trim(strtolower($cmd->getName()))] = $cmd;
	}
	public function dispatch(CommandSender $sender, $line){
		return $this->run($sender, explode(" ", $line));
	}
	public function run(CommandSender $sender, array $args){
		$showHelp = true;
		if(isset($args[0])){
			$cmd = strtolower(trim(array_shift($args)));
			if($cmd !== "help" and isset($this->cmds[$cmd])){
				return $this->cmds[$cmd]->run($sender, $args);
			}
		}
		if($showHelp){

			$sender->sendMessage($this->help(1, ($sender instanceof Player) ? 5:-1));
		}
		return true;
	}
	public function help($page = 1, $shown = -1){
		$data = $this->fullHelp();
		if($shown === -1){
			$max = 1;
		}
		else{
			$max = $this->helpPageSize($data, $shown);
		}
		$page = min($page, $max);
		$page = max($page, 1);
		if($shown === -1){
			$shown = $max;
		}
		$data = array_slice($data, ($page - 1) * $shown, $shown);
		$out = "";
		for($i = ($page - 1) * $shown; $i < $page * $shown and isset($data[$i]); $i++){
			$out .= $data[$i];
		}
		return $out;
	}
	public function fullHelp(){
		ksort($this->cmds, SORT_NATURAL|SORT_FLAG_CASE);
		$output = [];
		foreach($this->cmds as $name => $cmd){
			$out = "";
			$out .= Font::GOLD."/xecon $name";
			$out .= Font::BLUE." {$cmd->getUsage()} ";
			$out .= Font::GREEN." {$cmd->getDescription()}";
			$out .= Font::RESET."\n";
			$output[] = $out;
		}
		return $output;
	}
	public function getCommand($name){
		return $this->cmds[strtolower(trim($name))];
	}
	/**
	 * @param string[]|bool $data
	 * @param int $shown
	 * @return int
	 */
	public function helpPageSize($data = false, $shown = 5){
		if($data === false){
			$data = $this->fullHelp();
		}
		return (int) ceil(count($data) / $shown);
	}
}
