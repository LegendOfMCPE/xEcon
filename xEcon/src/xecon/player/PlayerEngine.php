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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use xecon\XEcon;

class PlayerEngine implements Listener{
	const PLAYER_TYPE = "legendsofmcpe.xecon.type.player";

	/** @var XEcon */
	private $xEcon;
	/** @var XEconSession[] */
	private $sessions = [];

	public function __construct(XEcon $xEcon){
		$this->xEcon = $xEcon;
		$xEcon->getServer()->getPluginManager()->registerEvents($this, $xEcon);
	}
	public function onLogin(PlayerLoginEvent $event){
		$this->sessions[$event->getPlayer()->getId()] = new XEconSession($this->xEcon, $event->getPlayer());
	}
	public function onQuit(PlayerQuitEvent $event){
		if(isset($this->sessions[$event->getPlayer()->getId()])){
			$this->sessions[$event->getPlayer()->getId()]->close();
			unset($this->sessions[$event->getPlayer()->getId()]);
		}
	}
}
