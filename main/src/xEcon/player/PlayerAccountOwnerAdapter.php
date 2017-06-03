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
use pocketmine\utils\TextFormat;
use xEcon\AccountOwner;
use xEcon\AccountOwnerAdapter;
use xEcon\AccountTransaction;
use xEcon\event\xEconAccountFormatEvent;

class PlayerAccountOwnerAdapter implements AccountOwnerAdapter{
	const OWNER_TYPE = "xEcon.Player";

	/** @var Player */
	private $player;
	/** @var AccountOwner */
	private $owner;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function bind(AccountOwner $owner){
		$this->owner = $owner;
	}

	public function isValid() : bool{
		return $this->player->isOnline();
	}

	public function testPaymentAccess(Player $player, AccountTransaction $transaction) : bool{
		return $player === $this->player or
			$player->hasPermission("xecon.admin.payas"); // TODO register perms
	}

	public function notifyPayment(AccountTransaction $transaction){
		$event = new xEconAccountFormatEvent($this->owner->getPlugin(), $transaction->getToType(), $transaction->getToName(), $transaction->getToAccount(), $transaction->getToAccountType());
		$this->owner->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		$this->player->sendMessage(TextFormat::YELLOW . sprintf("You are paying %s%g%s from your %s account to %s with %g%% fee",
				$event->getPrependedUnit(), $transaction->getFromLoss(), $event->getAppendedUnit(), $transaction->getFromAccount(), $event->getDisplayName(), $transaction->getRelativeInterest() * 100));
	}

	public function notifyRecipient(AccountTransaction $transaction){
		$event = new xEconAccountFormatEvent($this->owner->getPlugin(), $transaction->getFromType(), $transaction->getFromName(), $transaction->getFromAccount(), $transaction->getFromAccountType());
	}
}
