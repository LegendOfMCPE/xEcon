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
	private $inventory;
	public function __construct($name, $amount, Entity $entity, Inventory $inventory = null){
		$this->name = $name;
		$this->amount = $amount;
		$this->entity = $entity;
		$this->inventory = (!($inventory instanceof Inventory)) ? new DummyInventory($this):$inventory;
	}
	public function getName(){
		return $this->name;
	}
	public function getAmount(){
		return $this->amount;
	}
	public function add($amount){
		$this->setAmount($this->getAmount() + $amount);
	}
	public function take($amount){
		$this->setAmount($this->getAmount() - $amount);
	}
	public function setAmount($amount){
		$this->amount = $amount;

	}
	public function getInventory(){
		return $this->inventory;
	}
}
