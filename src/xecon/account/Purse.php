<?php

namespace xecon\account;


use pocketmine\item\Item;

class Purse extends MoneyContainerItem{
	public function __construct($meta = 0, $cnt = 1){
		parent::__construct("todo", $meta, $cnt, "Purse", 5);
	}
}
