<?php

namespace xecon\provider;

class EntityNotCreatedException extends \RuntimeException{
	public static function throwEx(){
		throw new self;
	}
	public function __construct(){
		parent::__construct("Entity not created on the database");
	}
}
