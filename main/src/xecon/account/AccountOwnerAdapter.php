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

namespace xecon\account;

use pocketmine\Player;

/**
 * This interface defines a set of methods that connect an {@see AccountOwner}'s data to other contexts.
 */
interface AccountOwnerAdapter{
	/**
	 * Returns whether this adapter is still valid, i.e. something in other contexts, e.g. the player context (the
	 * player is online), the plugin context (the plugin is enabled) is connected to the account owner. If this returns
	 * true, the account owner instance will be de-referenced from the cache.
	 *
	 * TODO decide: in a plugin that lets players make pawnbrokers, should the adapter for the paying account be declared online?
	 *
	 * @return bool
	 */
	public function isValid() : bool;

	/**
	 * Bind to an AccountOwner object.
	 *
	 * This method should only be called from the AccountOwner class.
	 *
	 * @param AccountOwner $owner
	 */
	public function bind(AccountOwner $owner);

	/**
	 * Checks whether the specified player has permission to create payments on behalf of this AccountOwner using the
	 * given Account.
	 *
	 * @param Player  $player
	 * @param Account $account where $account->getOwner() is the owner that this AccountOwnerAdapter is bound to.
	 *
	 * @return bool
	 */
	public function hasPaymentAccess(Player $player, Account $account) : bool;

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

	/**
	 * Notify the account owner of a generic message.
	 *
	 * TODO: support translations
	 *
	 * @param string $message
	 */
	public function notify(string $message);
}
