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

namespace xecon;

use mysqli;
use pocketmine\plugin\PluginBase;
use RuntimeException;
use xecon\account\Account;
use xecon\economy\Economy;
use xecon\entity\EconomicEntity;
use xecon\provider\DataProvider;
use xecon\provider\mysql\MysqlProvider;
use xecon\provider\sqlite3\Sqlite3Provider;

class XEcon extends PluginBase{
	/** @var Economy[] */
	private $economies = [];

	public function onEnable(){
		$this->saveDefaultConfig();
		foreach($this->getConfig()->get("economies") as $economy){
			$this->economies[$economy["name"]] = new Economy($this, $economy);
		}
		$this->getDefaultEconomy(); // validate
	}

	public function getEconomy($name){
		return isset($this->economies[$name]) ? $this->economies[$name] : null;
	}
	public function getEconomies(){
		return $this->economies;
	}
	public function getDataProvider(array $config) : DataProvider{
		$name = $config["name"];
		$options = $config["options"];
		switch($name){
			case "sqlite":
			case "sqlite3":
				$path = $options["path"];
				return new Sqlite3Provider($this, $path);
			case "mysql":
				$cd = $options["connection-details"];
				$tablePrefix = $options["table_prefix"];
				return new MysqlProvider($this, $cd, $tablePrefix);
		}
		throw new RuntimeException("Unknown data provider type: " . $name);
	}
	public static function getMysqli(array $connectionDetails){
		$host = $connectionDetails["host"];
		$username = $connectionDetails["username"];
		$password = $connectionDetails["password"];
		$schema = $connectionDetails["schema"];
		$port = isset($connectionDetails) ? $connectionDetails["port"] : 3306;
		return new mysqli($host, $username, $password, $schema, $port);
	}
	public function getDefaultPlayerAccounts(EconomicEntity $entity){
		$decs = $this->getConfig()->getNested("players.default-accounts.{$entity->getEconomy()->getName()}");
		$accounts = [];
		foreach($decs as $name => $details){
			$accounts[strtolower($name)] = new Account(strtolower($name), $details["value"], $details["min-value"], $details["max-value"], false, []);
		}
		return $accounts;
	}
	public function getDefaultEconomy(){
		if(isset($this->economies[$this->getConfig()->get("default-economy")])){
			return $this->economies[$this->getConfig()->get("default-economy")];
		}
		throw new RuntimeException("Undefined default-economy");
	}
}
