<?php

namespace xecon\commands;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as Font;

class MyCommandMap{
	/**
	 * @var Subcommand[] $cmds
	 */
	protected $cmds;
	public function register(Subcommand $cmd){
		$this->cmds[strtolower($cmd->getName())] = $cmd;
	}
	public function run(CommandSender $sender, array $args){
		$cmd = strtolower(trim(array_shift($args)));
		if(isset($this->cmds[$cmd])){
			return $this->cmds[$cmd]->run($sender, $args);
		}
		$sender->sendMessage("Subcommand ".Font::GOLD."/xecon $cmd".Font::RESET." doesn't exist! Use /xecon help");
		return true;
	}
	public function help($page = 1){

	}
	public function fullHelp(){
		ksort($this->cmds, SORT_NATURAL|SORT_FLAG_CASE);
		$out = "";
		foreach($this->cmds as $name => $cmd){
			$out .= Font::GOLD."/xecon $name";
			$out .= Font::BLUE." {$cmd->getUsage()} ";
			$out .= Font::GREEN." {$cmd->getDescription()}";
			$out .= Font::RESET."\n";
		}
	}
}
