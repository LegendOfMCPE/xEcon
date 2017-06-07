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

namespace xecon\service;

use pocketmine\Player;
use xecon\account\Account;
use xecon\account\AccountOwner;
use xecon\account\AccountOwnerAdapter;
use xecon\account\AccountTransaction;

class ServiceAccountOwnerAdapter implements AccountOwnerAdapter{
	const TYPE = "service";

	/** @var AccountOwner|void */
	private $owner;

	public function isValid() : bool{
		return true;
	}

	public function bind(AccountOwner $owner){
		$this->owner = $owner;
	}

	/**
	 * Checks whether the specified player has permission to create payments on behalf of this AccountOwner using the
	 * given Account.
	 *
	 * @param Player  $player
	 * @param Account $account where $account->getOwner() is the owner that this AccountOwnerAdapter is bound to.
	 *
	 * @return bool
	 */
	public function hasPaymentAccess(Player $player, Account $account) : bool{
		return $player->hasPermission("xecon.admin.accctrl");
	}

	/**
	 * Notify, through any means, the context behind this adapter, that a transaction has just occurred, and the source
	 * account belongs to this account owner.
	 *
	 * @param AccountTransaction $transaction
	 */
	public function notifyPayment(AccountTransaction $transaction){
		// TODO: Implement notifyPayment() method.
	}

	/**
	 * Notify, through any means, the context behind this adapter, that a transaction has just occurred, and the target
	 * account belongs to this account owner.
	 *
	 * @param AccountTransaction $transaction
	 */
	public function notifyRecipient(AccountTransaction $transaction){
		// TODO: Implement notifyRecipient() method.
	}

	/**
	 * Notify the account owner of a generic message.
	 *
	 * TODO: support translations
	 *
	 * @param string $message
	 */
	public function notify(string $message){
		// TODO: Implement notify() method.
	}
}
