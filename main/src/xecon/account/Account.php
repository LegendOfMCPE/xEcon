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

use pocketmine\Server;
use xecon\modifier\AccountModifier;
use xecon\xEcon;

final class Account{
	const PATH_SEPARATOR = ":";

	/** @var AccountOwner */
	private $owner;
	/** @var string */
	private $name;
	/** @var int */
	private $lastFinalize;
	/** @var float */
	private $balance;
	/** @var AccountModifier[]|null[] */
	private $modifiers = [];

	/** @var int */
	private $loadTime;
	private $balanceChanged = false;
	private $removedModifiers = [];
	private $newModifiers = [];

	public function __construct(AccountOwner $owner, string $name, int $lastFinalize, float $balance){
		$this->owner = $owner;
		xEcon::validate(mb_strlen($name) <= 40, "Account name is too long");
		$this->name = $name;
		$this->lastFinalize = $lastFinalize;
		$this->balance = $balance;
		$this->loadTime = time();
	}

	/**
	 * @internal Only to be called from Database::loadAccounts()
	 *
	 * @param string $modifierName
	 * @param int    $additionTime
	 */
	public function initModifier(string $modifierName, int $additionTime){
		$this->modifiers[$modifierName] = AccountModifier::getModifier($this, $modifierName, $additionTime);
	}

	/**
	 * @internal Only to be called from Database::loadAccounts()
	 */
	public function inited(){
		foreach($this->modifiers as $modifier){
			$modifier->onLoad();
		}
	}

	public function getServer() : Server{
		return $this->owner->getPlugin()->getServer();
	}

	public function getPlugin() : xEcon{
		return $this->owner->getPlugin();
	}

	public function getOwner() : AccountOwner{
		return $this->owner;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getAbsoluteName(){
		return $this->getOwner()->getType() . Account::PATH_SEPARATOR . $this->getOwner()->getName() . Account::PATH_SEPARATOR . $this->name;
	}

	public function getLoadTime(){
		return $this->loadTime;
	}

	public function getLastFinalize(){
		return $this->lastFinalize;
	}

	public function getBalance() : float{
		return $this->balance;
	}

	public function setBalance(float $balance){
		foreach($this->modifiers as $modifier){
			if($modifier->isDummyAccount() || !$modifier->canSetBalance($balance)){
				return;
			}
		}
		$this->balance = $balance;
		$this->balanceChanged = true;
	}

	/**
	 * @return string[]
	 */
	public function getModifiers() : array{
		return array_keys($this->modifiers);
	}

	public function applyModifier(AccountModifier $modifier){
		if($modifier->getAccount() !== $this){
			throw new \RuntimeException("AccountModifier is applied to the wrong account");
		}
		if(isset($this->modifiers[$modifier->getName()])){
			throw new \RuntimeException("Attempt to apply an existing modifier to an account");
		}
		$this->modifiers[$modifier->getName()] = $modifier;
		$this->newModifiers[$modifier->getName()] = true;
		$modifier->onApply();
	}

	public function removeModifier(AccountModifier $modifier){
		if($modifier->getAccount() !== $this){
			throw new \RuntimeException("AccountModifier is applied to the wrong account");
		}
		$modifier->onRemove();
		unset($this->modifiers[$modifier->getName()]);
		if(isset($this->newModifiers[$modifier->getName()])){
			unset($this->newModifiers[$modifier->getName()]);
		}else{
			$this->removedModifiers[$modifier->getName()] = true;
		}
		$modifier->invalidate();
	}

	public function finalize(){
		$this->lastFinalize = time();
		// TODO delete removed modifiers
		// TODO save new modifiers
		foreach($this->modifiers as $modifier){
			$modifier->onFinalize();
		}
		// TODO check balanceChanged
	}
}
