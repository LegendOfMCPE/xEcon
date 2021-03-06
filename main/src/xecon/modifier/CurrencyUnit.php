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

interface CurrencyUnit{
	/**
	 * Express the amount using this unit.
	 *
	 * @param float $amount
	 *
	 * @return string
	 */
	public function format(float $amount) : string;
}
