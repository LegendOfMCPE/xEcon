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

use libasynql\mysql\DirectMysqlQueryTask;
use libasynql\mysql\MysqlCredentials;
use libasynql\mysql\MysqlUtils;
use libasynql\result\SqlErrorResult;
use libasynql\result\SqlResult;
use libasynql\result\SqlSelectResult;
use xecon\account\Account;
use xecon\account\AccountOwner;
use xecon\modifier\AccountModifier;
use xecon\xEcon;

class MysqlDatabase implements Database{
	/** @var xEcon */
	private $xEcon;
	/** @var MysqlCredentials */
	private $credentials;

	public function __construct(xEcon $xEcon){
		$this->xEcon = $xEcon;
		$this->credentials = MysqlCredentials::fromArray($xEcon->getConfig()->getNested("database.mysql"));
		MysqlUtils::init($xEcon, $this->credentials);
		// TODO init tables
	}

	public function loadAccounts(AccountOwner $owner, callable $accountsAcceptor, callable $onFailure = null){
		$task = new DirectMysqlQueryTask($this->credentials, /** @lang MySQL */
			"SELECT a.acc_name, balance, UNIX_TIMESTAMP(last_finalize) last_finalize, am.modifier_name, am.addition_time
				FROM xecon_accounts a
				LEFT JOIN xecon_account_modifiers am ON a.owner_type = am.owner_type AND a.owner_name = am.owner_name AND a.acc_name = am.acc_name
			WHERE a.owner_type = ? AND a.owner_name = ?", [["s", $owner->getType()], ["s", $owner->getName()]], function(SqlResult $result) use ($accountsAcceptor, $owner, $onFailure){
				if(!($result instanceof SqlSelectResult)){
					assert($result instanceof SqlErrorResult);
					if($onFailure !== null){
						$onFailure($result->getException());
					}else{
						$this->xEcon->getLogger()->logException($result->getException());
					}
					return;
				}
				$result->fixTypes([
					"acc_name" => SqlSelectResult::TYPE_STRING,
					"balance" => SqlSelectResult::TYPE_FLOAT,
					"last_finalize" => SqlSelectResult::TYPE_INT,
					"modifier_name" => SqlSelectResult::TYPE_STRING
				]);
				/** @var Account[] $accounts */
				$accounts = [];
				foreach($result->rows as $row){
					$row = (object) $row;
					if(!isset($accounts[$row->acc_name])){
						$accounts[$row->acc_name] = new Account($owner, $row->acc_name, $row->last_finalize, $row->balance);
					}
					$accounts[$row->acc_name]->initModifier($row->modifier_name, $row->addition_time);
				}
				foreach($accounts as $account){
					$account->inited();
				}
				$accountsAcceptor($accounts);
			});
		$this->xEcon->getServer()->getScheduler()->scheduleAsyncTask($task);
	}

	/**
	 * @param Account[] $accounts
	 */
	public function addAccounts(array $accounts){
		if(count($accounts) === 0){
			return;
		}
		$query = "INSERT INTO xecon_accounts (owner_type, owner_name, acc_name, balance, last_finalize) VALUES ";
		$args = [];
		foreach($accounts as $account){
			$query .= "(?, ?, ?, ?, ?),";
			$args[] = ["s", $account->getOwner()->getType()];
			$args[] = ["s", $account->getOwner()->getName()];
			$args[] = ["s", $account->getName()];
			$args[] = ["d", $account->getBalance()];
			$args[] = ["i", time()];
		}
		$query = substr($query, 0, -1);
		$task = new DirectMysqlQueryTask($this->credentials, $query, $args);
		$this->xEcon->getServer()->getScheduler()->scheduleAsyncTask($task);
	}

	public function removeAccounts(string $type, string $name, array $accountNames){
		if(count($accountNames) === 0){
			return;
		}
		$query = "DELETE FROM xecon_accounts WHERE owner_type = ? AND owner_name = ? AND acc_name IN (" .
			substr(str_repeat(",?", count($accountNames)), 1) . ")";
		$args = [["s", $type], ["s", $name]];
		foreach($accountNames as $account){
			$args[] = ["s", $account];
		}
		$task = new DirectMysqlQueryTask($this->credentials, $query, $args);
		$this->xEcon->getServer()->getScheduler()->scheduleAsyncTask($task);
	}

	/**
	 * @param AccountModifier[] $modifiers
	 */
	public function addModifiers(array $modifiers){
		if(count($modifiers) === 0){
			return;
		}
		$query = "INSERT INTO xecon_account_modifiers (owner_type, owner_name, acc_name, modifier_name, addition_time) VALUES (" .
			substr(str_repeat(",(?, ?, ?, ?, ?)", count($modifiers)), 1) . ")";
		$args = [];
		foreach($modifiers as $modifier){
			$args[] = ["s", $modifier->getAccount()->getOwner()->getType()];
			$args[] = ["s", $modifier->getAccount()->getOwner()->getName()];
			$args[] = ["s", $modifier->getAccount()->getName()];
			$args[] = ["s", $modifier->getName()];
			$args[] = ["i", $modifier->getAdditionTime()];
		}
		$task = new DirectMysqlQueryTask($this->credentials, $query, $args);
		$this->xEcon->getServer()->getScheduler()->scheduleAsyncTask($task);
	}

	/**
	 * @param Account  $account
	 * @param string[] $modifierNames
	 */
	public function removeModifiers(Account $account, array $modifierNames){
		if(count($modifierNames) === 0){
			return;
		}
		$query = "DELETE FROM xecon_account_modifiers WHERE owner_type = ? AND owner_name = ? AND acc_name = ? AND modifier_name IN (" .
			substr(str_repeat(",?", count($modifierNames)), 1) . ")";
		$args = [["s", $account->getOwner()->getType()], ["s", $account->getOwner()->getName()], ["s", $account->getName()]];
		foreach($modifierNames as $modifierName){
			$args[] = ["s", $modifierName];
		}
		$task = new DirectMysqlQueryTask($this->credentials, $query, $args);
		$this->xEcon->getServer()->getScheduler()->scheduleAsyncTask($task);
	}

	/**
	 * @param string    $type
	 * @param string    $name
	 * @param Account[] $accounts
	 */
	public function updateAccounts(string $type, string $name, array $accounts){
		if(count($accounts) === 0){
			return;
		}

		$nameList = substr(str_repeat(",?", count($accounts)), 1);
		$balanceMap = str_repeat("WHEN ? THEN ? ", count($accounts));
		$query = /** @lang MySQL */
			"UPDATE xecon_accounts SET balance = CASE acc_name $balanceMap END, last_finalize = CURRENT_TIMESTAMP
				WHERE owner_type = ? AND owner_name = ? AND acc_name IN ($nameList)";
		$args = [];
		foreach($accounts as $account){
			$args[] = ["s", $account->getName()];
			$args[] = ["d", $account->getBalance()];
		}
		foreach($accounts as $account){
			$args[] = ["s", $account->getName()];
		}
		$task = new DirectMysqlQueryTask($this->credentials, $query, $args);
		$this->xEcon->getServer()->getScheduler()->scheduleAsyncTask($task);
	}

	public function close(){
		MysqlUtils::closeAll($this->xEcon, $this->credentials);
	}
}
