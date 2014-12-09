<?php

namespace xecon\account;

use xecon\entity\Entity;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;

class Loan implements Transactable{
	/** @var int */
	protected $due;
	/** @var Account */
	protected $creditor;
	/** @var int */
	protected $creation;
	protected $increasePerHour;
	protected $lastInterestUpdate;
	protected $originalAmount;
	public function __construct(Account $creditor, $amount, Entity $owner, $due, $increasePerHour, $name = false, $creation = false, $originalAmount = false, $lastInterestUpdate = false){
		// setting defaults
		if(!($creditor->getName() instanceof Service) and !($creditor->getEntity() instanceof PlayerEnt)){
			throw new \InvalidArgumentException("Loan must be provided by a player or a service");
		}
		if($creation === false){
			$creation = time();
		}
		if(!is_string($name)){
			$name = sprintf("Loan from {$creditor->getUniqueName()}");
		}
		$oname = $name;
		for($i = 2; $owner->getAccount($name) instanceof Account; $i++){
			$name = "$oname ($i)";
		}
		if(!is_numeric($originalAmount)){
			$originalAmount = $amount;
		}
		if(!is_int($lastInterestUpdate)){
			$lastInterestUpdate = time();
		}
		// saving fields
		$this->name = $name;
		$this->creditor = $creditor;
		$this->amount = $amount;
		$this->owner = $owner;
		$this->due = $due;
		$this->increasePerHour = $increasePerHour;
		$this->creation = $creation;
		$this->originalAmount = $originalAmount;
		$this->lastInterestUpdate = $lastInterestUpdate;
	}
	public function getName(){
		return $this->name;
	}
	public function setName($name){
		$this->name = $name;
	}
	public function getDue(){
		return $this->due;
	}
	public function setDue($due){
		$this->due = $due;
	}
	public function updateInterest(){
		$now = time();
		$ratio = ($now - $this->lastInterestUpdate) / 3600;
		$this->amount *= (1 + $this->increasePerHour) ** $ratio;
		$this->lastInterestUpdate = $now;
	}
	public function getInterest(){
		return $this->getAmount() - $this->originalAmount;
	}
	public function getAmount(){
		$this->updateInterest();
		return $this->amount;
	}
	public function isExpired(){
		return $this->due < time();
	}
	public function getHourlyIncrease(){
		return $this->increasePerHour;
	}
	public function getHourlyPercentageIncrease(){
		return $this->increasePerHour * 100;
	}
	public function getOwner(){
		return $this->owner;
	}
	public function getCreditor(){
		return $this->creditor;
	}
	public function getCreationTime(){
		return $this->creation;
	}
	public function getOriginalAmount(){
		return $this->originalAmount;
	}
	public function getLastInterestUpdate(){
		return $this->increasePerHour;
	}
	public function canPay($amount){
		return false; // loan accounts can only be repaid, not added to
	}
	public function canReceive($amount){
		$this->updateInterest();
		return $this->amount >= $amount;
	}
	public function pay(Transactable $other, $amount, $details = "None", $force = false){
		return false;
	}
	public function add($amount){
		$this->updateInterest();
		$this->amount += $amount;
	}
	public function take($amount){
		$this->updateInterest();
		$this->amount -= $amount;
	}
/*
	public function setName($name){
		$this->name = $name;
	}
//	public function toArray(){
//		$this->updateInterest();
//		$data = parent::toArray();
//		$data["due"] = $this->due;
//		$data["creditor"] = $this->creditor->getUniqueName();
//		$data["increase per hour"] = $this->increasePerHour;
//		$data["creation"] = $this->creation;
//		$data["original amount"] = $this->originalAmount;
//		$data["last interest update"] = $this->lastInterestUpdate;
//		return $data;
//	}
	public function getDue(){
		return $this->due;
	}
	public function setDue($due){
		$this->due = $due;
	}
	public function isExpired(){
		return $this->due < time();
	}
	public function getOriginalAmount(){
		return $this->originalAmount;
	}
	public function getCreation(){
		return $this->creation;
	}
	public function updateInterest(){
		$hours = (time() - $this->lastInterestUpdate) / 3600;
		$this->amount *= pow(1 + $this->increasePerHour, $hours);
		$this->lastInterestUpdate = time(); // how could I have forgotten this!
	}
	public function getIncreasePerHour(){
		return $this->increasePerHour;
	}
	public function getCreditor(){
		return $this->creditor;
	}
	public function __set($k, $v){
		if($k === "amount"){
			$this->updateInterest();
		}
		$this->{$k} = $v;
	}
	public function __get($k){
		if($k === "amount"){
			$this->updateInterest();
		}
		return $this->{$k};
	}
	public function getLastInterestUpdate(){
		$this->updateInterest();
		return $this->lastInterestUpdate;
	}
	*/
}
