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

namespace xecon\account;

use pocketmine\Server;
use xecon\xEcon;

final class AccountOwner{
	/** @var xEcon */
	private $xEcon;
	/** @var bool */
	private $loading = true;

	/** @var string */
	private $type;
	/** @var string */
	private $name;
	/** @var Account[] */
	private $accounts;
	/** @var AccountOwnerAdapter|null */
	private $adapter = null;

	/** @var bool[] */
	private $newAccounts = [], $removedAccounts = [];

	private function __construct(xEcon $xEcon, string $type, string $name){
		$this->xEcon = $xEcon;
		xEcon::validate(preg_match(/** @lang RegExp */
				'@^[A-Za-z0-9_.\-]{1,120}$@', $type) === 1, "Account owner type has illegal characters");
		$this->type = $type;
		xEcon::validate(mb_strpos($name, Account::PATH_SEPARATOR) === false, "Account owner name must not contain colon");
		xEcon::validate(mb_strlen($name) <= 70, "Account owner name is too long");
		$this->name = $name;
		$xEcon->getAccountOwnerCache()->cache($this);
	}

	public function getPlugin() : xEcon{
		return $this->xEcon;
	}

	public function getServer() : Server{
		return $this->xEcon->getServer();
	}

	public function isLoading() : bool{
		return $this->loading;
	}

	/**
	 * Returns the type of this account owner. The type should contain unique identifiers for the plugin declaring this
	 * type and a human-readable description of what this type is.
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

	public function addAccount(Account $account){
		$this->accounts[] = $account;
		$this->newAccounts[$account->getName()] = true;
	}

	public function removeAccount(string $name){
		if(isset($this->accounts[$name])){
			if(isset($this->newAccounts[$name])){
				unset($this->newAccounts[$name]);
			}else{
				$this->removedAccounts[$name] = true;
			}
			unset($this->accounts[$name]);
		}else{
			throw new \InvalidArgumentException("No such account to delete");
		}
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
	 * @param callable|null            $onSuccess accepts an AccountOwner
	 * @param callable|null            $onFailure accepts an \Exception
	 * @param AccountOwnerAdapter|null $adapter
	 */
	public static function load(xEcon $xEcon, string $type, string $name, callable $onSuccess = null, callable $onFailure = null, AccountOwnerAdapter $adapter = null){
		$instance = new AccountOwner($xEcon, $type, $name);
		$xEcon->getDataBase()->loadAccounts($instance, function($accounts) use ($instance, $onSuccess, $adapter){
			$instance->accounts = $accounts;
			$instance->setAdapter($adapter);
			$instance->loading = false;
			$onSuccess($instance);
		}, function(\Exception $e) use ($instance, $onFailure){
			$instance->loading = false;
			$onFailure($e);
		});
	}

	public function finalize(){
		$this->xEcon->getDataBase()->removeAccounts($this->type, $this->name, array_keys($this->removedAccounts));
		$this->xEcon->getDataBase()->addAccounts(array_map(function($string){
			return $this->accounts[$string];
		}, $this->newAccounts));
		foreach($this->accounts as $account){
			if(!isset($this->newAccounts[$account->getName()])){
				$account->finalize();
			}
		}
	}
}
