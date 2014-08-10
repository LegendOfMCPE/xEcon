<?php

namespace xecon\log;

use xecon\account\Account;

class Transaction{
	/** @var string */
	private $fromType, $fromName, $fromAccount, $toType, $toName, $toAccount;
	/** @var double */
	private $amount;
	/** @var string */
	private $details;
	public function __construct($fromType, $fromName = null, $fromAccount = null, $toType, $toName = null, $toAccount = null, $amount, $details, $timestamp = null){
		if($fromType instanceof Account){
			$fromName = $fromType->getEntity()->getName();
			$fromAccount = $fromType->getName();
			$fromType = $fromType->getEntity()->getAbsolutePrefix();
		}
		$this->fromType = $fromType;
		$this->fromName = $fromName;
		$this->fromAccount = $fromAccount;
		if($toType instanceof Account){
			$toName = $toType->getEntity()->getName();
			$toAccount = $toType->getName();
			$toType = $toType->getEntity()->getAbsolutePrefix();
		}
		$this->toType = $toType;
		$this->toName = $toName;
		$this->toAccount = $toAccount;
		$this->amount = $amount;
		$this->details = $details;
		$this->timestamp = is_int($timestamp) ? $timestamp:time();
	}
	/**
	 * @return string
	 */
	public function getFromType(){
		return $this->fromType;
	}
	/**
	 * @return string
	 */
	public function getFromName(){
		return $this->fromName;
	}
	/**
	 * @return string
	 */
	public function getFromAccount(){
		return $this->fromAccount;
	}
	/**
	 * @return string
	 */
	public function getToType(){
		return $this->toType;
	}
	/**
	 * @return string
	 */
	public function getToName(){
		return $this->toName;
	}
	/**
	 * @return string
	 */
	public function getToAccount(){
		return $this->toAccount;
	}
	/**
	 * @return float
	 */
	public function getAmount(){
		return $this->amount;
	}
	/**
	 * @return string
	 */
	public function getDetails(){
		return $this->details;
	}
}
