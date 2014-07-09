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
	 * @param Plugin $plugin
	 * @param callable $callback
	 * @param mixed[] $args
	 */
	public function __construct(Plugin $plugin, callable $callback, array $args = []){
		parent::__construct($plugin);
		$this->callback = $callback;
		$this->args = $args;
	}
	public function onRun($ticks){
		call_user_func_array($this->callback, $this->args);
	}
}
