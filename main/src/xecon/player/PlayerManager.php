<?php

/*
 *
 * xEcon
 *
 * Copyright (C) 2017 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
*/

namespace xecon\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginException;
use xecon\modifier\AccountModifier;
use xecon\utils\StringUtils;
use xecon\xEcon;

class PlayerManager implements Listener{
	/** @var xEcon */
	private $xEcon;
	/** @var PlayerAccountOwnerAdapter[] */
	private $sessions = [];

	public function __construct(xEcon $xEcon){
		$this->xEcon = $xEcon;

		foreach($xEcon->getConfig()->getNested("player.modifiers", []) as $name => $data){
			if(isset($data["interest"])){
				$margins = [-INF, INF];
				foreach($data["interest"] as &$interest){
					if(!isset($interest["compound"], $interest["every"])){
						throw new PluginException("Interest must contain 'compound' and 'every'");
					}
					$interest["compound"] = $compound = (float) $interest["compound"];
					if($compound <= 0 || $compound == 1){
						throw new \UnexpectedValueException("Compound ratio must be positive and must not be equal to 1");
					}
					$interest["every"] = StringUtils::ui_inputToSecs($interest["every"]);

					if(isset($interest["if"])){
						$interest["if"] = StringUtils::ui_rangeToFunction($interest["if"], $margins);
					}else{
						$interest["if"] = function(){
							return true;
						};
					}

					$interest["priority"] = (float) $interest["priority"];
				}
				usort($data["interest"], function($i1, $i2) : int{
					return $i1["priority"] <=> $i2["priority"];
				});
				$margins = array_unique($margins, SORT_NUMERIC);
				sort($margins, SORT_NUMERIC);
				$data["margins"] = $margins;
			}
			PlayerAccountModifier::$modifierConfigs[$name] = $data;
			AccountModifier::registerModifier(PlayerAccountModifier::class, $name);
		}

		$this->xEcon->getServer()->getPluginManager()->registerEvents($this, $xEcon);
	}

	/**
	 * @param PlayerLoginEvent $event
	 *
	 * @priority        MONITOR
	 * @ignoreCancelled true
	 */
	public function onJoin(PlayerLoginEvent $event){
		$this->sessions[$event->getPlayer()->getId()] = new PlayerAccountOwnerAdapter($this->xEcon, $event->getPlayer());
	}

	public function onQuit(PlayerQuitEvent $event){
		if(isset($this->sessions[$event->getPlayer()->getId()])){
			$owner = $this->sessions[$event->getPlayer()->getId()]->getOwner();
			if($owner !== null){
				$owner->finalize();
			}
			unset($this->sessions[$event->getPlayer()->getId()]);
		}
	}
}
