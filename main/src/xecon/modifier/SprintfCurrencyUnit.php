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

namespace xecon\modifier;

class SprintfCurrencyUnit implements CurrencyUnit{
	/** @var string */
	private $format;

	public function __construct(string $format){
		$this->format = $format;
	}

	public function format(float $amount) : string{
		return sprintf($this->format, $amount);
	}
}
