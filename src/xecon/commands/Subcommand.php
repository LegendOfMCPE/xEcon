<?php

namespace xecon\commands;

use pocketmine\command\CommandSender;

abstract class Subcommand{
	/**
	 * @return string
	 */
	public abstract function getName();
	/**
	 * @return string
	 */
	public abstract function getDescription();
	/**
	 * @return string
	 */
	public abstract function getUsage();
	/**
	 * @param CommandSender $issuer
	 * @param array $args
	 * @return bool|string
	 */
	public function run(CommandSender $issuer, array $args){
		$result = $this->onRun($issuer, $args);
		switch(true){
			case is_bool($result):
				if($result === false){
					$issuer->sendMessage("Usage: /xecon $this {$this->getUsage()} {$this->getDescription()}");
				}
				return true;
			case is_string($result):
				$issuer->sendMessage($result);
				return true;
			default:
				return true;
		}
	}
	protected abstract function onRun(CommandSender $issuer, array $args);
	public function __toString(){
		return $this->getName();
	}
}
