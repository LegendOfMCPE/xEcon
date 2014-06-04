<?php

namespace xecon\account;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use xecon\entity\Entity;

class Account implements InventoryHolder{
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var float
	 */
	private $amount;
	/**
	 * @var \xecon\entity\Entity
	 */
	private $entity;
	/**
	 * @var \pocketmine\inventory\Inventory
	 */
	private $inventory;
	private $maxContainable = 1000;
	public function __construct($name, $amount, Entity $entity, Inventory $inventory = null){
		$this->name = $name;
		$this->amount = $amount;
		$this->entity = $entity;
		$this->inventory = (!($inventory instanceof Inventory)) ? new DummyInventory($this):$inventory;
	}
	public function getMaxContainable(){
		return $this->maxContainable;
	}
	public function setMaxContainable($cnt){
		$this->maxContainable = $cnt;
	}
	public function getName(){
		return $this->name;
	}
	public function getAmount(){
		return $this->amount;
	}
	public function add($amount){
		return $this->setAmount($this->getAmount() + $amount);
	}
	public function take($amount){
		return $this->setAmount($this->getAmount() - $amount);
	}
	public function setAmount($amount){
		if($amount > $this->maxContainable){
			return false;
		}
		$this->amount = $amount;
		return true;
	}
	public function getInventory(){
		return $this->inventory;
	}
}
