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

namespace xecon\player;

use pocketmine\Player;
use xecon\entity\EconomicEntity;
use xecon\XEcon;

class XEconSession{
	/** @var Player */
	private $player;
	/** @var EconomicEntity[] */
	private $entities;
	public function __construct(XEcon $xEcon, Player $player){
		$this->player = $player;
		foreach($xEcon->getConfig()->getNested("players.default-accounts") as $economyName => $a){
			$economy = $xEcon->getEconomy($economyName);
			if($economy === null){
				throw new \RuntimeException("Unable to start player session - economy $economyName does not exist.");
			}
			$this->entities[$economyName] = $ent = new EconomicEntity($xEcon, $economy, PlayerEngine::PLAYER_TYPE, $this->getName(), false);
			$ent->setDefaultAccounts($xEcon->getDefaultPlayerAccounts($ent));
			$ent->reload();
		}
	}
	public function getName(){
		return strtolower($this->player->getName());
	}
	public function close(){
		foreach($this->entities as $ent){
			$ent->close();
		}
	}
}
