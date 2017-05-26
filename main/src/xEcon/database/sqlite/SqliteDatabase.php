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

namespace xEcon\database\sqlite;

use libasynql\sqlite\SqliteUtils;
use xEcon\database\Database;
use xEcon\xEcon;

class SqliteDatabase implements Database{
	/** @var xEcon */
	private $xEcon;
	private $file;

	public function __construct(xEcon $xEcon){
		$this->xEcon = $xEcon;
		$this->file = $xEcon->getDataFolder() . "xecon.sqlite3";
	}

	public function loadAccounts(string $type, string $name, callable $onSuccess = null, callable $onFailure = null){
		// TODO: Implement loadAccounts() method
	}

	public function close(){
		SqliteUtils::closeAll($this->xEcon, $this->file);
	}
}
