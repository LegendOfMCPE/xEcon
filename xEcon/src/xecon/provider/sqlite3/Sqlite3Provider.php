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

namespace xecon\provider\sqlite3;

use SQLite3;
use xecon\entity\EconomicEntity;
use xecon\provider\DataProvider;
use xecon\XEcon;

class Sqlite3Provider implements DataProvider{
	/** @var XEcon */
	private $xEcon;
	/** @var SQLite3 */
	private $db;

	public function __construct(XEcon $xEcon, $path){
		$this->xEcon = $xEcon;
		$cwd = getcwd(); // I read this as "coward" every time :D
		chdir($xEcon->getDataFolder());
		$wasFile = is_file($path);
		if(!$wasFile){
			touch($path);
		}
		$realPath = realpath($path);
		if(!$wasFile){
			unlink($path);
		}
		chdir($cwd);
		$this->db = new SQLite3($realPath);
		$this->init();
	}
	private function init(){
		// TODO
	}
	public function loadEntity(EconomicEntity $entity){
// TODO
	}
	public function saveEntity(EconomicEntity $entity){
		// TODO: Implement saveEntity() method.
	}
}
