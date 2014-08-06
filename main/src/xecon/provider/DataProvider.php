<?php

namespace xecon\provider;

use xecon\entity\Entity;
use xecon\Main;

trait DataProvider{
	/** @var Main */
	private $main;
	public function __construct(Main $main){
		$this->main = $main;
	}
	/**
	 * @return Main
	 */
	public function getMain(){
		return $this->main;
	}
	public abstract function loadEntity(Entity $entity);
	public abstract function saveEntity(Entity $entity);
	public abstract function deleteEntity($uniqueName);
	public function close(){

	}
	public function isAvailable(){
		return true;
	}
}
