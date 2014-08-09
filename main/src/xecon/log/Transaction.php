<?php

namespace xecon\log;

class Transaction{
	/** @var string */
	private $fromType, $fromName, $fromAccount, $toType, $toName, $toAccount;
	/** @var double */
	private $amount;
	/** @var string */
	private $details;
	public function __construct($fromType, $fromName, $fromAccount, $toType, $toName, $toAccount, $amount, $details){
		$this->fromType = $fromType;
		$this->fromName = $fromName;
		$this->fromAccount = $fromAccount;
		$this->toType = $toType;
		$this->toName = $toName;
		$this->toAccount = $toAccount;
		$this->amount = $amount;
		$this->details = $details;
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
