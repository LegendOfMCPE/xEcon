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

use pocketmine\event\Cancellable;
use xEcon\AccountTransaction;
use xEcon\xEcon;

class xEconTransactionEvent extends xEconEvent implements Cancellable{
	/** @var AccountTransaction */
	private $transaction;

	public function __construct(xEcon $xEcon, AccountTransaction $transaction){
		parent::__construct($xEcon);
		$this->transaction = $transaction;
	}

	public function getTransaction() : string{
		return $this->transaction;
	}
}
