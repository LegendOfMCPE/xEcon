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

namespace xEcon;

class Account{
	const PATH_SEPARATOR = ":";

	/** @var AccountOwner */
	private $owner;
	/** @var string */
	private $name;
	/** @var float */
	private $balance;
	/** @var bool */
	private $isLiability;

	public function __construct(AccountOwner $owner, string $name, float $balance, bool $isLiability = false){
		$this->owner = $owner;
		xEcon::validate(mb_strlen($name) <= 40, "Account name is too long");
		$this->name = $name;
		$this->balance = $balance;
		$this->isLiability = $isLiability;
	}

	public function getOwner() : AccountOwner{
		return $this->owner;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getBalance() : float{
		return $this->balance;
	}

	public function isLiability() : bool{
		return $this->isLiability;
	}
}
