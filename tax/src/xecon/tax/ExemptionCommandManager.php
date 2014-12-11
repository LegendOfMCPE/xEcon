<?php

namespace xecon\tax;

class ExemptionCommandManager{
	/** @var ExemptionCommand[] */
	private $cmds = [];
	public function __construct(TaxPlugin $plugin){
		$this->plugin = $plugin;
	}
	public function register(ExemptionCommand $cmd){
		$this->cmds[$cmd->getName()] = $cmd;
	}
	public function getValue($exp){
		list($cmd, $args) = explode(":", $exp, 2);
		if(isset($this->cmds[$cmd])){
			return $this->cmds[$cmd]->getValue($args);
		}
		throw new \RuntimeException("Undefined exemption command: $exp");
	}
}
