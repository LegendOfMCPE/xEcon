<?php

/*
 * xEcon
 *
 * Copyright (C) 2015 LegendsOfMCPE and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

namespace xecon\account;

class Account{
	/** @var string */
	private $name;
	/** @var double */
	private $value;
	/** @var double */
	private $minValue, $maxValue;
	/** @var bool */
	private $loan;
	/** @var array */
	private $metadata = [];

	public function __construct(string $name, double $value, double $minValue, double $maxValue, bool $isLoan, array $metadata){
		$this->name = $name;
		$this->value = $value;
		$this->minValue = $minValue;
		$this->maxValue = $maxValue;
		$this->loan = $isLoan;
		$this->metadata = $metadata;
	}

	public function getName() : string{
		return $this->name;
	}
	public function getValue() : double{
		return $this->value;
	}
	public function getMinValue() : double{
		return $this->minValue;
	}
	public function getMaxValue() : double{
		return $this->maxValue;
	}
	public function isLoan() : bool{
		return $this->loan;
	}
	public function setMetadata(string $key, $data){
		$this->metadata[$key] = $data;
	}
	public function getMetadata(string $key){
		return $this->metadata[$key];
	}
}
