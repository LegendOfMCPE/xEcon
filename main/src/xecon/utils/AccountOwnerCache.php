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

use xecon\account\Account;
use xecon\account\AccountOwner;
use xecon\xEcon;

/**
 * Caches AccountOwner instances fetched from the database.
 *
 * The cache is cleared every five minutes, removing all cached AccountOwner instances that have been stored for more
 * than one minute, except for those that have a {@see AccountOwnerAdapter::isValid() valid} {@see AccountOwnerAdapter}.
 */
class AccountOwnerCache{
	/** @var xEcon */
	private $xEcon;

	/** @var \xecon\account\AccountOwner[] */
	private $store = [];
	/** @var int[] */
	private $storeTime;

	public function __construct(xEcon $xEcon){
		$this->xEcon = $xEcon;
		$task = new CallbackPluginTask($xEcon, [$this, "clean"]);
		$xEcon->getServer()->getScheduler()->scheduleDelayedRepeatingTask($task, 6000, 6000);
	}

	public function cache(AccountOwner $owner){
		$this->store[$key = $owner->getType() . Account::PATH_SEPARATOR . $owner->getName()] = $owner;
		$this->storeTime[$key] = time();
	}

	/**
	 * @param string $type
	 * @param string $name
	 *
	 * @return null|AccountOwner
	 */
	public function fetch(string $type, string $name){
		return $this->store[$type . Account::PATH_SEPARATOR . $name] ?? null;
	}

	public function clean(){
		foreach($this->store as $key => $owner){
			if(!$owner->isLoading() and !$owner->hasValidAdapter() and time() - $this->storeTime[$key] > 60){
				$owner->finalize();
				unset($this->store[$key]);
				unset($this->storeTime[$key]);
			}
		}
	}
}
