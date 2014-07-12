<?php

namespace xecon\utils;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

class CallbackPluginTask extends PluginTask{
	/** @var callable */
	protected $callback;
	/** @var mixed[] */
	private $args;
	/**
	 * @var callable
	 */
	private $onCancel;
	/**
	 * @var array|\mixed[]
	 */
	private $cancelArgs;
	/**
	 * @param Plugin $plugin
	 * @param callable $callback
	 * @param mixed[] $args
	 * @param callable $onCancel
	 * @param mixed[] $cancelArgs
	 */
	public function __construct(Plugin $plugin, callable $callback, array $args = [], callable $onCancel = null, array $cancelArgs = []){
		parent::__construct($plugin);
		$this->callback = $callback;
		$this->args = $args;
		$this->onCancel = $onCancel;
		$this->cancelArgs = $cancelArgs;
	}
	public function onRun($ticks){
		call_user_func_array($this->callback, $this->args);
	}
	public function onCancel(){
		if(is_callable($this->onCancel)){
			call_user_func_array($this->onCancel, $this->cancelArgs);
		}
	}
}
