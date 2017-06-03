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

class xEconAccountFormatEvent extends xEconAccountEvent{
	public static $handlerList = null;

	/** @var string */
	private $displayName;
	/** @var string */
	private $prependedUnit = '$';
	/** @var string */
	private $appendedUnit = "";

	public function __construct(xEcon $xEcon, string $ownerType, string $ownerName, string $accountName, string $accountType, float $currentBalance){
		parent::__construct($xEcon, $ownerType, $ownerName, $accountName, $accountType, $currentBalance);
		$this->displayName = "$ownerType:$ownerName:$accountName";
	}

	public function getDisplayName() : string{
		return $this->displayName;
	}

	public function setDisplayName(string $displayName){
		$this->displayName = $displayName;
	}

	public function getPrependedUnit() : string{
		return $this->prependedUnit;
	}

	public function setPrependedUnit(string $prependedUnit){
		$this->prependedUnit = $prependedUnit;
	}

	public function getAppendedUnit() : string{
		return $this->appendedUnit;
	}

	public function setAppendedUnit(string $appendedUnit){
		$this->appendedUnit = $appendedUnit;
	}
}
