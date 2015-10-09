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

namespace xecon\economy;

use xecon\provider\DataProvider;
use xecon\XEcon;

class Economy{
	/** @var XEcon */
	private $xEcon;
	/** @var string */
	private $name;
	/** @var DataProvider */
	private $dataProvider;

	public function __construct(XEcon $xEcon, array $config){
		$this->xEcon = $xEcon;
		$this->name = $config["name"];
		$this->dataProvider = $xEcon->getDataProvider($config["data-provider"]);
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @return DataProvider
	 */
	public function getDataProvider(){
		return $this->dataProvider;
	}
}
