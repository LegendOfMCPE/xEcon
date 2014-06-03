<?php

namespace xecon\account;

use pocketmine\inventory\CustomInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\InventoryType;
use xecon\Main;

class DummyInventory extends CustomInventory{
	public function __construct(InventoryHolder $holder, $name = "Money Account"){
		parent::__construct($holder, clone InventoryType::get(InventoryType::CHEST), [], 36, $name);
	}
	/**
	 * @return Main
	 */
	public function getPlugin(){ // in case this is added, which I think is very possible
		return Main::get();
	}
}
