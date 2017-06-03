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

use xEcon\xEcon;

class xEconAccountEvent extends xEconEvent{
	/** @var string */
	private $ownerType;
	/** @var string */
	private $ownerName;
	/** @var string */
	private $accountName;
	/** @var string */
	private $accountType;
	/** @var float */
	private $currentBalance;

	public function __construct(xEcon $xEcon, string $ownerType, string $ownerName, string $accountName, string $accountType, float $currentBalance){
		parent::__construct($xEcon);
		$this->ownerType = $ownerType;
		$this->ownerName = $ownerName;
		$this->accountName = $accountName;
		$this->accountType = $accountType;
		$this->currentBalance = $currentBalance;
	}

	public function getOwnerType() : string{
		return $this->ownerType;
	}

	public function getOwnerName() : string{
		return $this->ownerName;
	}

	public function getAccountName() : string{
		return $this->accountName;
	}

	public function getAccountType() : string{
		return $this->accountType;
	}

	public function getCurrentBalance() : float{
		return $this->currentBalance;
	}
}
