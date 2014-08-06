<?php

namespace xecon\shops\shops;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use xecon\shops\Shops;

class PhysicalShop extends Position{
	use Shop{
		__construct as shop_construct;
	}
	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param Level $level
	 * @param Shops $shops
	 * @param Item $item
	 * @param bool $sell
	 * @param int $amount
	 * @param double $price
	 * @param bool $checkDamage
	 * @param int|bool $id
	 */
	public function __construct($x, $y, $z, Level $level, Shops $shops, Item $item, $sell, $amount, $price, $checkDamage = true, $id = false){
		parent::__construct($x, $y, $z, $level);
		$this->shop_construct($shops, $item, $sell, $amount, $price, $checkDamage, $id);
	}
}
