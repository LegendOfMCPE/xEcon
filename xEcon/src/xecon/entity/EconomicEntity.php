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

namespace xecon\entity;

use xecon\account\Account;
use xecon\economy\Economy;
use xecon\XEcon;

class EconomicEntity{
	/** @var XEcon */
	private $xEcon;
	/** @var Economy */
	private $economy;
	/** @var string */
	private $type, $name;
	/** @var Account[]|null */
	private $accounts = null;
	/** @var bool */
	private $loaded = false;

	private $defaultAccounts;

	public function __construct(XEcon $xEcon, Economy $economy, string $type, string $name, bool $load = true){
		$this->xEcon = $xEcon;
		$this->economy = $economy;
		$this->type = $type;
		$this->name = $name;
		if($load){
			$this->reload();
		}
	}
	public function reload(){
		$this->economy->getDataProvider()->loadEntity($this);
	}
	public function isLoaded() : bool{
		return $this->loaded;
	}
	public function close(){
		if($this->loaded){
			$this->economy->getDataProvider()->saveEntity($this);
		}
	}
	/**
	 * Returns {@code null} if the entity is not loaded.
	 * @return Account[]|null
	 */
	public function getAccounts(){
		return $this->accounts;
	}
	/**
	 * @return Economy
	 */
	public function getEconomy(){
		return $this->economy;
	}
	/**
	 * @return string
	 */
	public function getType(){
		return $this->type;
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	public function getFullUniqueName(){
		return $this->economy->getName() . "/" . $this->type . "/" . $this->name;
	}

	/**
	 * @param Account[]|null $accounts
	 * @internal
	 */
	public function reloadCallback($accounts){
		$this->loaded = true;
		if($accounts === null){
			$this->accounts = $this->defaultAccounts;
		}else{
			$this->accounts = $accounts;
		}
	}
	/**
	 * @return mixed
	 */
	public function getDefaultAccounts(){
		return $this->defaultAccounts;
	}
	/**
	 * @param Account[] $accounts
	 */
	public function setDefaultAccounts(array $accounts){
		$this->defaultAccounts = $accounts;
	}
}
