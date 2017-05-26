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

namespace xEcon\database\mysql;

use libasynql\mysql\MysqlCredentials;
use libasynql\mysql\MysqlUtils;
use xEcon\database\Database;
use xEcon\xEcon;

class MysqlDatabase implements Database{
	/** @var xEcon */
	private $xEcon;
	/** @var MysqlCredentials */
	private $cred;

	public function __construct(xEcon $xEcon){
		$this->xEcon = $xEcon;
		$this->cred = MysqlCredentials::fromArray($xEcon->getConfig()->getNested("database.mysql"));
		MysqlUtils::init($this->xEcon, $this->cred);
	}

	public function loadAccounts(string $type, string $name, callable $onSuccess = null, callable $onFailure = null){
		// TODO: Implement loadAccounts() method.
	}

	public function close(){
		MysqlUtils::closeAll($this->xEcon, $this->cred);
	}
}
