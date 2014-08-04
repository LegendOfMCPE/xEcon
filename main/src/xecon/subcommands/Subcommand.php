<?php

namespace xecon\subcommands;

use pocketmine\command\CommandSender;
use xecon\Main;

abstract class Subcommand{
	const NO_PLAYER = 2;
	const NO_ACCOUNT = 3;
	const WRONG_USE = false;
	const NO_PERM = true;
	private $main;
	public function __construct(Main $main){
		$this->main = $main;
	}
	public abstract function getName();
	public abstract function getDescription();
	public abstract function getUsage();
	public function execute(CommandSender $sender, array $args){
		$result = $this->run($sender, $args);
		if($result === null){
			return;
		}
		if(is_string($result)){
			$sender->sendMessage($result);
			return;
		}
		if(is_bool($result)){
			if($result){
				$sender->sendMessage("You don't have permission to use this command.");
			}
			else{
				$sender->sendMessage("Usage: ".$this->getUsage());
			}
			return;
		}
		if(is_int($result)){
			switch($result){
				case self::NO_ACCOUNT:
					$sender->sendMessage("Such account doesn't exist.");
					break;
				case self::NO_PLAYER:
					$sender->sendMessage("This player is not online.");
			}
			return;
		}
	}
	protected abstract function run(CommandSender $sender, array $args);
	/**
	 * @return Main
	 */
	public function getMain(){
		return $this->main;
	}
	public function getAliases(){
		return [];
	}
}
