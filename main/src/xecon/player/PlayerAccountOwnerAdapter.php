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

use pocketmine\Player;
use xecon\account\Account;
use xecon\account\AccountOwner;
use xecon\account\AccountOwnerAdapter;
use xecon\account\AccountTransaction;
use xecon\modifier\AccountModifier;
use xecon\xEcon;

/**
 * @internal This is an internal class for managing player accounts. Other plugins should not use this class at all.
 */
class PlayerAccountOwnerAdapter implements AccountOwnerAdapter{
	const TYPE = "player";

	/** @var xEcon */
	private $xEcon;
	/** @var Player */
	private $player;
	/** @var AccountOwner|void */
	private $owner;

	public function __construct(xEcon $xEcon, Player $player){
		$this->player = $player;
		$xEcon->loadOrGetOwner(PlayerAccountOwnerAdapter::TYPE, strtolower($player->getName()), function(AccountOwner $owner){
			if(count($owner->getAccounts()) === 0){
				$config = $this->xEcon->getConfig()->getNested("player.defaultAccounts");
				foreach($config as $name => $prop){
					$account = new Account($owner, $name, time(), (float) $prop["balance"]);
					foreach($prop["modifiers"] as $modifierName){
						$modifier = AccountModifier::getModifier($account, $modifierName, time());
						$account->applyModifier($modifier);
					}
					$owner->addAccount($account);
				}
			}
		}, $this);
		$this->xEcon = $xEcon;
	}

	public function isValid() : bool{
		return $this->player->isOnline();
	}

	/**
	 * @return AccountOwner|null
	 */
	public function getOwner(){
		return $this->owner ?? null;
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
