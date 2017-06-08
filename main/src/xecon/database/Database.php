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

namespace xecon\database;

use xecon\account\Account;
use xecon\account\AccountOwner;
use xecon\modifier\AccountModifier;

interface Database{
	public function loadAccounts(AccountOwner $owner, callable $accountsAcceptor, callable $onFailure = null);

	/**
	 * @param Account[] $accounts
	 */
	public function addAccounts(array $accounts);

	/**
	 * @param string   $type
	 * @param string   $name
	 * @param string[] $accountNames
	 */
	public function removeAccounts(string $type, string $name, array $accountNames);

	/**
	 * @param AccountModifier[] $modifiers
	 */
	public function addModifiers(array $modifiers);

	/**
	 * @param Account  $account
	 * @param string[] $modifiers
	 */
	public function removeModifiers(Account $account, array $modifiers);

	/**
	 * @param string    $type
	 * @param string    $name
	 * @param Account[] $accounts
	 */
	public function updateAccounts(string $type, string $name, array $accounts);

	public function close();
}
