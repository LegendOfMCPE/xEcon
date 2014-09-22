<?php

namespace xecon\cmd;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use xecon\Main;

abstract class XEconCommand extends Command implements PluginIdentifiableCommand{
	private $main;
	public function __construct(Main $main){
		$a = $this->getAliases_();
		if(!is_array($a)){
			$a = [$a];
		}
		parent::__construct($this->getName_(), $this->getDesc_(), $this->getUsage_(), $a);
		$this->main = $main;
	}
	protected abstract function getName_();
	protected abstract function getDesc_();
	protected abstract function getUsage_();
	protected function getAliases_(){
		return [];
	}
	public function getPlugin(){
		return $this->main;
	}
	public function execute(CommandSender $sender, $alias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(($r = $this->execute_($sender, $args)) === false){
			$sender->sendMessage($this->getUsage_());
			return false;
		}
		if(is_string($r)){
			$sender->sendMessage($r);
		}
		return true;
	}
	public abstract function execute_(CommandSender $sender, array $args);
}
