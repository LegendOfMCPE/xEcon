<?php

/*
 *
 * xEcon
 *
 * Copyright (C) 2017 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
*/

namespace xEcon\player;

use pocketmine\Player;
use xEcon\AccountOwnerAdapter;
use xEcon\AccountTransaction;

class PlayerAccountAdapter implements AccountOwnerAdapter{
	/** @var Player */
	private $player;

	public function __construct(Player $player){
		$this->player = $player;
	}
	public function isValid() : bool{
		return $this->player->isOnline();
	}

	public function testPaymentAccess(Player $player, AccountTransaction $transaction) : bool{
		return $player === $this->player or
			$player->hasPermission("xecon.admin.payas"); // TODO register perms
	}

	public function notifyPayment(AccountTransaction $transaction){
		// TODO: Implement notifyPayment() method.
	}

	public function notifyRecipient(AccountTransaction $transaction){
		// TODO: Implement notifyRecipient() method.
	}
}
