<?php

/*
 * xEcon
 *
 * Copyright (C) 2015 LegendsOfMCPE and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

namespace xecon\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use xecon\XEcon;

class XEconCommand extends Command implements PluginIdentifiableCommand{
	/** @var XEcon */
	private $xEcon;

	public function __construct(XEcon $xEcon, string $name, string $description, string $usage, string $permission, string ...$aliases){
		parent::__construct($name, $description, $usage, $aliases);
		$this->setPermission($permission);
		$this->xEcon = $xEcon;
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		try{
			$result = $this->run($args, $sender);
			if(is_string($result)){
				$sender->sendMessage($result);
			}elseif($result === false){
				$sender->sendMessage($this->getUsage());
			}
			return true;
		}catch(\Exception $e){
			// TODO report
			return false;
		}
	}
	protected abstract function run(array $args, CommandSender $sender);

	/**
	 * @return XEcon
	 */
	public function getXEcon(){
		return $this->xEcon;
	}
	/**
	 * @return XEcon
	 */
	public function getPlugin(){
		return $this->xEcon;
	}
}
