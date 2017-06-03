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

final class AccountOwner{
	/** @var xEcon */
	private $xEcon;

	/** @var string */
	private $type;
	/** @var string */
	private $name;
	/** @var Account[] */
	private $accounts;
	/** @var AccountOwnerAdapter|null */
	private $adapter = null;

	/** @var bool */
	private $hasChanges;

	private function __construct(xEcon $xEcon, string $type, string $name){
		$this->xEcon = $xEcon;
		xEcon::validate(preg_match(/** @lang RegExp */
				'@^[A-Za-z0-9_.\-]{1,120}$@', $type) === 1, "Account owner type has illegal characters");
		$this->type = $type;
		xEcon::validate(mb_strpos($name, Account::PATH_SEPARATOR) === false, "Account owner name must not contain colon");
		xEcon::validate(mb_strlen($name) <= 70, "Account owner name is too long");
		$this->name = $name;
	}
	// TODO: 1. Store to AccountOwnerCache
	// TODO: 2. Support multiple account adapters

	public function getPlugin() : xEcon{
		return $this->xEcon;
	}

	/**
	 * Returns the type of this account owner. The type should contain unique identifiers for the plugin declaring this
	 * type and a human-readable description of what this type is. Account owners of the same type do not need to have
	 * a consistent type of {@see AccountOwnerAdapter}.
	 *
	 * @return string
	 */
	public function getType() : string{
		return $this->type;
	}

	/**
	 * Returns the name of this account owner. This name is unique among all account owners of the same
	 * {@see AccountOwner::getType() type}.
	 *
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * Returns all accounts (both capital and liabilities) for this account owner.
	 *
	 * @return Account[]
	 */
	public function getAccounts() : array{
		return $this->accounts;
	}

	/**
	 * Sets the adapter for this account owner, or unset it by passing null.
	 *
	 * @param AccountOwnerAdapter|null $adapter
	 *
	 * @return AccountOwner
	 */
	public function setAdapter(AccountOwnerAdapter $adapter = null) : AccountOwner{
		$this->adapter = $adapter;
		if($adapter instanceof AccountOwnerAdapter){
			$adapter->bind($this);
		}
		return $this;
	}

	/**
	 * @return AccountOwnerAdapter|null
	 */
	public function getAdapter(){
		if($this->adapter instanceof AccountOwnerAdapter and !$this->adapter->isValid()){
			$this->adapter = null;
		}
		return $this->adapter;
	}

	public function hasValidAdapter() : bool{
		return $this->getAdapter() !== null;
	}

	/**
	 * @param xEcon                    $xEcon
	 * @param string                   $type
	 * @param string                   $name
	 * @param Account[]                $accounts
	 * @param AccountOwnerAdapter|null $adapter
	 *
	 * @return AccountOwner
	 */
	public static function createNew(xEcon $xEcon, string $type, string $name, array $accounts, AccountOwnerAdapter $adapter = null) : AccountOwner{
		$inst = new AccountOwner($xEcon, $type, $name);
		$inst->accounts = $accounts;
		$inst->setAdapter($adapter);
		return $inst;
	}

	/**
	 * @param xEcon                    $xEcon
	 * @param string                   $type
	 * @param string                   $name
	 * @param callable|null            $onSuccess accepts an AccountOwner
	 * @param callable|null            $onFailure accepts a MysqlException
	 * @param AccountOwnerAdapter|null $adapter
	 */
	public static function load(xEcon $xEcon, string $type, string $name, callable $onSuccess = null, callable $onFailure = null, AccountOwnerAdapter $adapter = null){
		$inst = new AccountOwner($xEcon, $type, $name);
		$inst->setAdapter($adapter);
		$db = $xEcon->getDatabase();
		$db->loadAccounts($type, $name, function($accounts) use ($inst, $onSuccess){
			$inst->accounts = $accounts;
			$onSuccess($inst);
		}, $onFailure);
	}

	public function notifyChanges(){
		$this->hasChanges = true;
	}

	public function hasChanges() : bool{
		return $this->hasChanges;
	}

	public function finalize(){
		if($this->hasChanges()){
			// TODO save
		}
	}
}
