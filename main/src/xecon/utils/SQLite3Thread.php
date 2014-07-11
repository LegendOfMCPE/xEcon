<?php

namespace xecon\utils;

/**
 * Class ThreadedSQLite3
 * A hopefully thread-safe SQLite3 thread
 * @package xecon\utils
 */
class ThreadedSQLite3 extends \Thread{
	/** @var \SQLite3 */
	protected $db = null;
	/** @var string $filename */
	private $filename;
	/** @var int|null */
	private $flags;
	/** @var mixed */
	private $encryption_key;
	private $closed = false;
	protected $callQueue = [];
	/**
	 * @param string $filename
	 * @param int|null $flags
	 * @param mixed $encryption_key
	 */
	public function __construct($filename, $flags = null, $encryption_key = null){
		$this->filename = $filename;
		$this->flags = $flags;
		$this->encryption_key = $encryption_key;
	}
	public function run(){
		$this->db = new \SQLite3($this->filename, $this->flags, $this->encryption_key);
		while($this->closed === false){
			while(count($this->callQueue) > 0){
				$call = array_shift($this->callQueue);
				$fx = $call[0];
				$args = $call[1];
				$result = call_user_func_array(array($this->db, $fx), $args);
				if(isset($call[2])){
					call_user_func(array($this, $call[2]), $result, isset($call[3]) ? $call[3]:null);
				}
			}
		}
		$this->db->close();
	}
	/**
	 * @return bool
	 */
	public function close(){
		$this->closed = true;
	}
	/**
	 * @param string $query
	 * @return bool
	 */
	public function exec($query){
		$this->callQueue[] = ["exec", [$query]];
	}
	/**
	 * @param string $query
	 * @return \SQLite3Result
	 */
	public function query($query){
		$this->callQueue[] = ["query", [$query]];
	}
	// prepare($query) directly won't work because it has to return an object
	/**
	 * This function is in the constructor thread
	 * @param string $query
	 * @param array[] $vars
	 * @param callable $resultCallback this should preferrably be a thread-safe anonymous function
	 */
	public function prepare($query, $vars, $resultCallback){
		$this->callQueue[] = ["prepare", $query, "prepareResult", [$vars, $resultCallback]];
	}
	/**
	 * This function is in the SQLite3 thread
	 * @param \SQLite3Stmt $op
	 * @param $args
	 */
	protected function prepareResult(\SQLite3Stmt $op, $args){
		$vars = $args[0];
		$resultCallback = $args[1];
		foreach($vars as $var){
			$op->bindValue($var[0], $var[1], isset($var[2]) ? $var[2]:null);
		}
		$result = $op->execute();
		$data = [];
		while(($datum = $result->fetchArray()) !== false){
			$data[] = $datum;
		}
		call_user_func($resultCallback, serialize($data));
	}
}
