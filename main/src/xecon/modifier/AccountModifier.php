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

namespace xecon\modifier;

use pocketmine\Server;
use xecon\account\Account;
use xecon\xEcon;

/**
 * Each instance represents one type of modifier applied to a specific account. The same instance would not be applied
 * to the same account twice, even if removed.
 *
 * In the documentation below,
 * - "this account" refers to the account that $this instance is applied to.
 * - "this modifier instance" refers to $this instance.
 * - "this modifier" refers to this type of AccountModifier.
 */
abstract class AccountModifier{
	private static $knownModifiers = [];

	/** @var Account */
	private $account;
	/** @var int */
	private $additionTime;
	/** @var string */
	private $modifierName;

	/**
	 * @param string|AccountModifier $class
	 * @param string                 $modifierName
	 */
	public final static function registerModifier(string $class, string $modifierName){
		$ownerType = $class::getOwnerType();
		if(strpos($ownerType, Account::PATH_SEPARATOR) !== false || strpos($modifierName, Account::PATH_SEPARATOR) !== false){
			throw new \InvalidArgumentException("AccountOwner type and AccountModifier name must not contain the account path separator symbol (" . Account::PATH_SEPARATOR . ")");
		}
		if(!class_exists($class)){
			throw new \ClassNotFoundException;
		}
		$class = new \ReflectionClass($class);
		if(!$class->isSubclassOf(AccountModifier::class)){
			throw new \InvalidArgumentException("$class does not extend " . AccountModifier::class);
		}
		$constructor = $class->getConstructor();
		if($constructor->getNumberOfRequiredParameters() !== 3 || $constructor->getParameters()[0]->getClass()->getName() !== Account::class){
			throw new \InvalidArgumentException("Subclasses of $class should have exactly one required constructor of type " . Account::class);
		}
		self::$knownModifiers[$ownerType . Account::PATH_SEPARATOR] = $class;
	}

	/**
	 * @param Account $account
	 * @param string  $modifierName
	 *
	 * @param int     $additionTime
	 *
	 * @return null|AccountModifier
	 */
	public static function getModifier(Account $account, string $modifierName, int $additionTime){
		if(isset(self::$knownModifiers[$key = $account->getOwner()->getType() . Account::PATH_SEPARATOR . $modifierName])){
			$class = self::$knownModifiers[$key];
			return new $class($account, $modifierName, $additionTime);
		}else{
			return null;
		}
	}


	public function __construct(Account $account, int $additionTime, string $modifierName){
		$this->account = $account;
		$this->additionTime = $additionTime;
		$this->modifierName = $modifierName;
	}

	/**
	 * @internal Do not call this method except from Account::removeModifier().
	 */
	public final function invalidate(){
		unset($this->account);
	}

	/**
	 * Returns the account that this modifier instance is applied on.
	 *
	 * @return Account
	 */
	public final function getAccount() : Account{
		return $this->account;
	}

	public final function getPlugin() : xEcon{
		return $this->account->getOwner()->getPlugin();
	}

	public final function getServer() : Server{
		return $this->account->getOwner()->getPlugin()->getServer();
	}

	public final function getAdditionTime(){
		return $this->additionTime;
	}

	/**
	 * The string identifier for this type of AccountModifier.
	 *
	 * Each AccountOwner type has its own set of AccountModifier types, so this name only needs to be unique from other
	 * AccountModifier types for this AccountOwner type, but not other AccountOwner types
	 *
	 * @return string
	 *
	 * @see AccountModifier::registerModifier()
	 * @see AccountModifier::getModifier()
	 */
	public final function getName() : string{
		return $this->modifierName;
	}


	/** @noinspection PhpDocMissingThrowsInspection */
	/**
	 * Returns the AccountOwner type that this AccountModifier is targetted at
	 *
	 * @return string
	 */
	public static function getOwnerType() : string{
		throw new \Error("AccountModifier classes to be registered must implement getOwnerType()");
	}

	/**
	 * Returns false if this AccountModifier needs some time to fetch additional data (hence not initialized yet).
	 *
	 * Once isInitialized() returns true for an instance, it should always return true in all subsequent calls for the
	 * same instance.
	 *
	 * @return bool
	 */
	public function isInitialized() : bool{
		return true;
	}

	/**
	 * Returns the currency symbol that this account uses, null if this modifier does not determine the unit.
	 *
	 * @return CurrencyUnit|null
	 */
	public function getCurrencyUnit(){
		return null;
	}

	/**
	 * If an account is a dummy account, it can carry out transactions without having its balance changed.
	 *
	 * The account is only non-dummy if all modifiers return false in this method.
	 *
	 * @return bool true if this account must be dummy, false to skip this modifier
	 */
	public function isDummyAccount() : bool{
		return false;
	}

	/**
	 * Returns whether this account's balance can be set to the specified amount.
	 *
	 * This method is useful in setting a minimum required amount in accounts, allowing overdraft or setting an account
	 * balance capacity.
	 *
	 * The transaction will be cancelled if any modifiers return false.
	 *
	 * @param float $newBalance the balance of the account after the transaction
	 *
	 * @return bool
	 */
	public function canSetBalance(float $newBalance) : bool{
		return true;
	}

	/**
	 * Called after this modifier is newly applied to this account.
	 *
	 * This modifier may be applied to this account before, but the previous instance would have already been removed
	 * (onRemove() has already been called on it).
	 *
	 * Additional data fetching should be executed here. If the additional data are fetched asynchronously, make sure
	 * isInitialized() returns false until the additional data are ready.
	 *
	 * Either onApply() or onLoad() will be called for the same instance, but not both.
	 *
	 * @see AccountModifier::isInitialized()
	 * @see AccountModifier::onLoad()
	 */
	public function onApply(){
	}

	/**
	 * Called when this account (along with its owner) is loaded from database, while this modifier type was applied to this
	 * account before.
	 *
	 * Additional data fetching should be executed here. If the additional data are fetched asynchronously, make sure
	 * isInitialized() returns false until the additional data are ready.
	 *
	 * Either onApply() or onLoad() will be called for the same instance, but not both.
	 *
	 * @see AccountModifier::isInitialized()
	 * @see AccountModifier::onApply()
	 */
	public function onLoad(){
	}

	/**
	 * Called when this account (along with its owner) is finalized, and this modifier is still applied to this account.
	 *
	 * Either onRemove() or onFinalize() will be called for the same instance, but not both. After either method is
	 * called, the AccountModifier SHOULD no longer be strongly referenced.
	 *
	 * @see AccountModifier::onRemove()
	 */
	public function onFinalize(){
	}

	/**
	 * Called before this modifier instance is removed.
	 *
	 * Either onRemove() or onFinalize() will be called for the same instance, but not both. After either method is
	 * called, the AccountModifier SHOULD no longer be strongly referenced.
	 *
	 * @see AccountModifier::onFinalize()
	 */
	public function onRemove(){
	}
}
