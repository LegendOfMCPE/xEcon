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

namespace xecon\player;

use libasynql\exception\SqlException;
use pocketmine\Player;
use xecon\account\Account;
use xecon\account\AccountOwner;
use xecon\account\AccountOwnerAdapter;
use xecon\account\AccountTransaction;
use xecon\xEcon;

/**
 * @internal This is an internal class for managing player accounts. Other plugins should not use this class at all.
 */
class PlayerAccountOwnerAdapter implements AccountOwnerAdapter{
	const TYPE = "player";

	/** @var Player */
	private $player;
	/** @var AccountOwner|void */
	private $owner;

	public function __construct(xEcon $xEcon, Player $player){
		$this->player = $player;
		AccountOwner::load($xEcon, PlayerAccountOwnerAdapter::TYPE, strtolower($player->getName()), function(AccountOwner $owner){
			if(count($owner->getAccounts()) === 0){

			}
		}, function(SqlException $e) use($xEcon, $player){
			$xEcon->getLogger()->error("Failed to load data for player {$player->getName()}!");
			$xEcon->getLogger()->logException($e);
		}, $this);
	}

	public function isValid() : bool{
		return $this->player->isOnline();
	}

	public function bind(AccountOwner $owner){
		$this->owner = $owner;
	}

	public function hasPaymentAccess(Player $player, Account $account) : bool{
		return $player === $this->player or $player->hasPermission("xecon.admin.accctrl");
	}

	public function notifyPayment(AccountTransaction $transaction){
		// TODO: Implement notifyPayment() method.
	}

	public function notifyRecipient(AccountTransaction $transaction){
		// TODO: Implement notifyRecipient() method.
	}

	public function notify(string $message){
		$this->player->sendMessage($message);
	}
}
