<?php

namespace xecon\tax\taxes;

use pocketmine\plugin\Plugin;

class TaxType{
	/** @var string */
	private $name, $class;
	/** @var Plugin */
	private $plugin;
	/**
	 * @param string $name
	 * @param string $class class name of a subclass of xecon\tax\Tax
	 * @param Plugin $plugin
	 * @throws \InvalidArgumentException
	 */
	public function __construct($name, $class, Plugin $plugin){
		$this->name = $name;
		$cnstr = get_class()."()";
		try{
			$class = new \ReflectionClass($class);
		}
		catch(\ReflectionException $e){
			throw new \InvalidArgumentException("Argument 2 passed to $cnstr must be a qualified class name, \"$class\" given", 0, $e);
		}
		if($class->isAbstract()){
			throw new \InvalidArgumentException("Argument 2 passed to $cnstr must be a qualified class name of a non-abstract class");
		}
		if(!$class->isSubclassOf(__NAMESPACE__."\\Tax")){
			throw new \InvalidArgumentException("Argument 2 pased to $cnstr must be a qualified class name of a subclass of ".__NAMESPACE__."\\Tax, \"$class\" given");
		}
		$this->class = $class;
		$this->plugin = $plugin;
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @return string
	 */
	public function getClass(){
		return $this->class;
	}
	public function create(array $args){
		return $this->class->newInstance($args);
	}
	/**
	 * @return Plugin
	 */
	public function getPlugin(){
		return $this->plugin;
	}
}
