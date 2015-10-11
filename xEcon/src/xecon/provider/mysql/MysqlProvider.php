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

namespace xecon\provider\mysql;

use Threaded;
use xecon\entity\EconomicEntity;
use xecon\provider\DataProvider;
use xecon\XEcon;

class MysqlProvider implements DataProvider{
	/** @var XEcon */
	private $xEcon;
	/** @var string[]|int[] */
	private $connectionDetails;
	/** @var string */
	private $tablePrefix;
	/** @var bool */
	private $initialized;

	/** @var MysqlThread */
	private $mysqlThread;
	private $onCompletion = [];

	public function __construct(XEcon $xEcon, array $connectionDetails, string $tablePrefix){
//		$host = $connectionDetails["host"];
//		$username = $connectionDetails["username"];
//		$password = $connectionDetails["password"];
//		$schema = $connectionDetails["schema"];
//		$port = isset($connectionDetails) ? $connectionDetails["port"] : 3306;
//		$this->db = $xEcon->getMysqli($host, $username, $password, $schema, $port);
		$this->xEcon = $xEcon;
		$this->connectionDetails = $connectionDetails;
		$this->tablePrefix = $tablePrefix;
		$this->init();
	}
	private function init(){
		// TODO
	}

	public function isInitialized() : bool{
		return $this->initialized;
	}
	public function loadEntity(EconomicEntity $entity){
		// TODO
	}

	public function addQuery($query, $onCompletion = null){
		$id = $this->mysqlThread->addQuery($query);
		if(is_callable($onCompletion)){
			$this->onCompletion[$id] = $onCompletion;
		}
		return $id;
	}
	public function tick(){
		$queries = $this->mysqlThread->readQuery();
		foreach($queries as $id => $query){
			if(isset($this->onCompletion[$id])){
				$callable = $this->onCompletion[$id];
				$callable($query);
			}
		}
	}
	public function saveEntity(EconomicEntity $entity){
		// TODO: Implement saveEntity() method.
	}
}
