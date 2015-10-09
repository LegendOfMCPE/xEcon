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

use mysqli_result;
use pocketmine\Thread;
use Threaded;
use xecon\XEcon;

class MysqlThread extends Thread{
	/** @var Threaded */
	private $mainToThread;
	/** @var Threaded */
	private $threadToMain;
	/** @var int */
	private $nextQueryId = 0;
	/** @var bool */
	public $closed = false;
	/** @var array */
	private $connectionDetails;

	public function __construct(array $connectionDetails){
		$this->mainToThread = new Threaded;
		$this->threadToMain = new Threaded;
		$this->connectionDetails = $connectionDetails;
	}

	/**
	 * Call this ONLY from the main thread
	 * @param string $query
	 * @return int
	 */
	public function addQuery(string $query) : int{
		$id = $this->nextQueryId++;
		$this->mainToThread[] = [$id, $query];
		return $id;
	}
	public function readQuery() : array{
		$out = [];
		while($this->threadToMain->count() > 0){
			$query = $this->threadToMain->shift();
			$out[$query["query_id"]] = $query;
		}
		return $out;
	}

	public function run(){
		$db = XEcon::getMysqli($this->connectionDetails);
		while(!$this->closed){
			while($this->mainToThread->count() > 0){
				list($id, $query) = $this->mainToThread->shift();
				$db->error = null;
				$result = $db->query($query);
				$rows = [];
				if($result instanceof mysqli_result){
					while(is_array($row = $result->fetch_assoc())){
						$rows[] = $row;
					}
				}
				$out = [
					"query_id" => $id,
					"error" => $db->error,
					"rows" => $rows,
					"insert_id" => $db->insert_id,
					"affected_rows" => $db->affected_rows,
				];
				$this->threadToMain[] = $out;
			}
		}
	}

	public function isClosed() : bool{
		return $this->closed;
	}
	public function close(){
		$this->closed = true;
	}
}
