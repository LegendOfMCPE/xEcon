<?php

namespace xecon\utils;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;

class HttpLoop extends AsyncTask{
	public static function init($port, $ip = "0.0.0.0"){
		Server::getInstance()->getScheduler()->scheduleAsyncTask(new static($port, $ip));
	}
	public $isRunning = true;
	public function __construct($port, $ip = "0.0.0.0"){
		$this->port = $port;
		$this->ip = $ip;
	}
	public function onRun(){
		$error = "create socket";
		$this->sk = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(is_resource($this->sk)){
			$success = socket_bind($this->sk, $this->ip, $this->port);
			if($success !== false){
				$s2 = socket_listen($thi->sk, 5);
				if($s2 === false){
					$error = "listen";
				}
			}
			else{
				$error = "bind socket";
			}
		}
		else{
			$error = "create socket";
		}
		if(isset($error)){
			console("[ERROR] xEcon HTTP server unable to $error to ".$this->ip.":".$this->port);
			$this->setResult(true);
			$this->isRunning = false;
			return;
		}
		while($this->isRunning){
			$con = socket_accept($this->sk);
			$tokens = explode(" ", trim(socket_read($con, 208, )));
			if($tokens[0] === "GET"){
				// TODO
			}
		}
	}
}
