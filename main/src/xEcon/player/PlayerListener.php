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

namespace xEcon\player;

use pocketmine\event\Listener;
use xEcon\event\xEconAccountFormatEvent;
use xEcon\xEcon;

class PlayerListener implements Listener{
	// TODO session management

	/** @var xEcon */
	private $xEcon;

	public function __construct(xEcon $xEcon){
		$this->xEcon = $xEcon;
	}

	public function e_formatPlayerAccount(xEconAccountFormatEvent $event){
		if($event->getOwnerType() === PlayerAccountOwnerAdapter::OWNER_TYPE){
			
		}
	}
}
