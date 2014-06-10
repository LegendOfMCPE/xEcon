<?php

namespace xecon\account;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\network\protocol;
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
	/** @var int[] */
	private $inventoryMoneySlots = [];
	/**
	 * @param string $name
	 * @param float $amount
	 * @param Entity $entity
	 * @param Inventory|null $inventory
	 * @param string[] $containerTypes
	 */
	public function __construct($name, $amount, Entity $entity, Inventory $inventory = null, array $containerTypes = []){
		$this->name = $name;
		$this->amount = $amount;
		$this->entity = $entity;
		$this->inventory = (!($inventory instanceof Inventory)) ? new DummyInventory($this):$inventory;
		foreach($containerTypes as $type){
			$id = constant($type."::ID");
		}
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
		$old = $this->amount;
		$this->amount = $amount;
		$this->tidyInventory($old, $amount);
		return true;
	}
	public function getInventory(){
		return $this->inventory;
	}
	public function tidyInventory($old, $new){
		$this->clearInventoryMoney();
		$this->addInventoryMoney($new);
	}
	protected function clearInventoryMoney(){
		while(count($this->inventoryMoneySlots) > 0){
			$this->getInventory()->setItem(array_shift($this->inventoryMoneySlots), Item::get(0));
		}
	}
	protected function addInventoryMoney($amount){

	}
}
