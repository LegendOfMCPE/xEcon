<?php

namespace xecon\tax\tax;

class TaxType{
	private $name;
	public function __construct(\ReflectionClass $class){
		if(!($class->implementsInterface(__NAMESPACE__."\\Tax"))){
			throw new \RuntimeException("ReflectionClass passed to TaxType constructor must implement Tax, {$class->getName()} given.");
		}
		$this->name = call_user_func(array($class->getName(), "getName"));
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
}
