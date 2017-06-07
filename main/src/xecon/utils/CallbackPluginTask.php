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

namespace xecon\utils;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

class CallbackPluginTask extends PluginTask{
	private $callback;
	private $args;

	public function __construct(Plugin $owner, callable $callback, ...$args){
		parent::__construct($owner);
		$this->callback = $callback;
		$this->args = $args;
	}

	public function onRun($currentTick){
		$c = $this->callback;
		$c($currentTick, ...$this->args);
	}
}
