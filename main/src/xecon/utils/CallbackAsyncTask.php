<?php

namespace xecon\utils;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CallbackAsyncTask extends AsyncTask{
	/** @var callable */
	private $runnable, $onFinish;
	public function __construct(callable $runnable, callable $onFinish = null){
		$this->runnable = $runnable;
		$this->onFinish = $onFinish;
	}
	public function onRun(){
		call_user_func($this->runnable);
	}
	public function onCompletion(Server $server){
		call_user_func($this->onFinish, $this);
	}
}
