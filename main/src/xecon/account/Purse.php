<?php

namespace xecon\account;

class Purse extends MoneyContainerItem{
	const ID = 0x70D0; // TODO
	const PER_AMOUNT = 5;
	const MAX_STACK = 15;
	const NAME = "Purse";
	public function __construct($meta = 0, $cnt = 1){
		parent::__construct(self::ID, $meta, $cnt, self::NAME, self::PER_AMOUNT, self::MAX_STACK);
	}
}
