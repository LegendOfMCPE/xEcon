<?php

namespace xecon\account;

use pocketmine\inventory\CustomInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\InventoryType;

class DummyInventory extends CustomInventory{
	public function __construct(InventoryHolder $holder, $name = "Money Account"){
		parent::__construct($holder, clone InventoryType::get(InventoryType::CHEST), [], 36, $name);
	}
}
