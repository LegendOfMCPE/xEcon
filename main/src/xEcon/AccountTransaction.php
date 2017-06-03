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

class AccountTransaction{
	// immutable
	/** @var string */
	private $fromType;
	/** @var string */
	private $fromName;
	/** @var string */
	private $fromAccount;
	/** @var string */
	private $fromAccountType;
	/** @var float */
	private $fromCurrentBalance;
	/** @var string */
	private $toType;
	/** @var string */
	private $toName;
	/** @var string */
	private $toAccount;
	/** @var string */
	private $toAccountType;
	/** @var float */
	private $toCurrentBalance;

	// mutable
	/** @var float */
	private $fromLoss;
	/** @var float */
	private $toGain;

	public function __construct(string $fromType, string $fromName, string $fromAccount, string $fromAccountType, float $fromCurrentBalance, string $toType, string $toName, string $toAccount, string $toAccountType, float $toCurrentBalance, float $fromLoss, float $toGain){
		$this->fromType = $fromType;
		$this->fromName = $fromName;
		$this->fromAccount = $fromAccount;
		$this->fromAccountType = $fromAccountType;
		$this->fromCurrentBalance = $fromCurrentBalance;
		$this->toType = $toType;
		$this->toName = $toName;
		$this->toAccount = $toAccount;
		$this->toAccountType = $toAccountType;
		$this->toCurrentBalance = $toCurrentBalance;
		$this->fromLoss = $fromLoss;
		$this->toGain = $toGain;
	}

	public function getFromType() : string{
		return $this->fromType;
	}

	public function getFromName() : string{
		return $this->fromName;
	}

	public function getFromAccount() : string{
		return $this->fromAccount;
	}

	public function getFromAccountType() : string{
		return $this->fromAccountType;
	}

	public function getFromCurrentBalance() : float{
		return $this->fromCurrentBalance;
	}

	public function getToType() : string{
		return $this->toType;
	}

	public function getToName() : string{
		return $this->toName;
	}

	public function getToAccount() : string{
		return $this->toAccount;
	}

	public function getToAccountType() : string{
		return $this->toAccountType;
	}

	public function getToCurrentBalance() : float{
		return $this->toCurrentBalance;
	}

	public function getFromLoss() : float{
		return $this->fromLoss;
	}

	public function setFromLoss(float $fromLoss){
		$this->fromLoss = $fromLoss;
	}

	public function getFromFinalBalance() : float{
		return $this->fromCurrentBalance - $this->fromLoss;
	}

	public function getToGain() : float{
		return $this->toGain;
	}

	public function setToGain(float $toGain){
		$this->toGain = $toGain;
	}

	public function getToFinalBalance() : float{
		return $this->toCurrentBalance + $this->toGain;
	}

	public function getAbsoluteInterest() : float{
		return $this->fromLoss - $this->toGain;
	}

	public function getRelativeInterest() : float{
		return ($this->fromLoss - $this->toGain) / $this->fromLoss;
	}
}
