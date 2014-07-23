<?php

namespace xecon\account;

use xecon\entity\Entity;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;

class Loan extends Account{
	/** @var int */
	protected $due;
	/** @var Account */
	protected $creditor;
	/** @var int */
	protected $creation;
	protected $increasePerHour;
	protected $lastInterestUpdate;
	protected $originalAmount;
	public static function constructInstance($name, Entity $entity, $data){
		$creditor = explode("/", $data["creditor"]);
		if(strtolower($creditor[0]) === "server" and strtolower(substr($creditor[1], 0, 7)) === "service"){
			$creditor = $entity->getMain()->getService()->getAccount($creditor[2]);
		}
		elseif(strtolower($creditor[0]) === "player"){
			$creditor = $entity->getMain()->getPlayerEnt($creditor[1])->getAccount($creditor[2]);
		}
		return new Loan($creditor, $data["amount"], $entity, $data["due"], $data["increase per hour"], $name, $data["creation"], $data["original amount"], $data["last interest update"]);
	}
	/**
	 * @param Account $creditor
	 * @param float $amount
	 * @param Entity $owner
	 * @param int $due
	 * @param number $increasePerHour
	 * @param bool|string $name
	 * @param int|bool $creation
	 * @param int|bool $originalAmount
	 * @param int|bool $lastInterestUpdate
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Account $creditor, $amount, Entity $owner, $due, $increasePerHour, $name = false, $creation = false, $originalAmount = false, $lastInterestUpdate = false){
		if(!($creditor->getEntity() instanceof Service) and !($creditor->getEntity() instanceof PlayerEnt)){
			throw new \InvalidArgumentException("Loan must be provided by a player or a service");
		}
		if($creation === false){
			$creation = time();
		}
		if(!is_string($name)){
			$name = "Loan from {$creditor->getEntity()->getAbsolutePrefix()} {$creditor->getEntity()->getName()}: {$creditor->getName()}";
		}
		for($oname = $name, $i = 2; $owner->getAccount($name) instanceof Account; $i++){
			$name = "$oname ($i)";
		}
		parent::__construct($name, $amount, $owner);
		$this->setIsLiability(true);
		$this->due = $due;
		$this->creation = $creation;
		$this->creditor = $creditor;
		$this->increasePerHour = $increasePerHour;
		$this->lastInterestUpdate = $lastInterestUpdate;
		$this->originalAmount = $originalAmount;
	}
	public function setName($name){
		$this->name = $name;
	}
	public function toArray(){
		$this->updateInterest();
		$data = parent::toArray();
		$data["due"] = $this->due;
		$data["creditor"] = $this->creditor->getUniqueName();
		$data["increase per hour"] = $this->increasePerHour;
		$data["creation"] = $this->creation;
		$data["original amount"] = $this->originalAmount;
		$data["last interest update"] = $this->lastInterestUpdate;
		return $data;
	}
	/**
	 * @return int
	 */
	public function getDue(){
		return $this->due;
	}
	/**
	 * @param int $due
	 */
	public function setDue($due){
		$this->due = $due;
	}
	public function isExpired(){
		return $this->due < time();
	}
	public function getOriginalAmount(){
		return $this->originalAmount;
	}
	/**
	 * @return int
	 */
	public function getCreation(){
		return $this->creation;
	}
	public function setAmount($amount){
		parent::setAmount($amount);

	}
	public function updateInterest(){
		$hours = (time() - $this->lastInterestUpdate) / 3600;
		$this->amount += $hours * $this->increasePerHour;
	}
	public function __destruct(){
		$this->updateInterest();
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
}
