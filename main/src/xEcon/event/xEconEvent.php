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

namespace xEcon\event;

use pocketmine\event\plugin\PluginEvent;
use xEcon\xEcon;

class xEconEvent extends PluginEvent{
	public function __construct(xEcon $xEcon){
		parent::__construct($xEcon);
	}

	public function getPlugin() : xEcon{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::getPlugin();
	}
}
