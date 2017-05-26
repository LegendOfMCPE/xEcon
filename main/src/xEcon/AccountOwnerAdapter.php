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

namespace xEcon;

use pocketmine\Player;

/**
 * This interface defines a set of methods that connect an {@see AccountOwner}'s data to other contexts.
 */
interface AccountOwnerAdapter{
	/**
	 * Returns whether this adapter is still valid, i.e. something in other contexts (e.g. Server context) is connected
	 * to the account owner. If this returns true, the account owner instance will be de-referenced from the cache.
	 *
	 * @return bool
	 */
	public function isValid() : bool;

	/**
	 * Returns whether the given player is permitted to pay on behalf of this account owner. The provided
	 * AccountTransaction describes the account to transact from and the account that will be transacted into, as well
	 * as the amount of money.
	 *
	 * <code>$transaction->getFromType()</code> and <code>$transaction->getFromName()</code> must be identical to those
	 * of the account owner behind this adapter.
	 *
	 * @param Player             $player
	 * @param AccountTransaction $transaction
	 *
	 * @return bool
	 */
	public function testPaymentAccess(Player $player, AccountTransaction $transaction) : bool;

	/**
	 * Notify, through any means, the context behind this adapter, that a transaction has just occurred, and the source
	 * account belongs to this account owner.
	 *
	 * @param AccountTransaction $transaction
	 */
	public function notifyPayment(AccountTransaction $transaction);

	/**
	 * Notify, through any means, the context behind this adapter, that a transaction has just occurred, and the target
	 * account belongs to this account owner.
	 *
	 * @param AccountTransaction $transaction
	 */
	public function notifyRecipient(AccountTransaction $transaction);
}
