<?php

namespace xecon\account;

class DigitalPurse extends MoneyContainerItem{
	const ID = 0x70D0; // TODO
	const PER_AMOUNT = PHP_INT_MAX;
	const MAX_STACK = PHP_INT_MAX;
	const NAME = "Digital Purse";
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::ID, $meta, $count, self::NAME, self::PER_AMOUNT, self::MAX_STACK/*, false*/);
	}
}
