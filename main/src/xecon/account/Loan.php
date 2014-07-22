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
	public static function constructInstance($name, Entity $entity, $data){
		$creditor = explode("/", $data["creditor"]);
		if(strtolower($creditor[0]) === "server" and strtolower(substr($creditor[1], 0, 7)) === "service"){
			$creditor = $entity->getMain()->getService()->getAccount($creditor[2]);
		}
		elseif(strtolower($creditor[0]) === "player"){

		}
		return new Loan($creditor, $data["amount"], $entity, $data["due"], $name);
	}
	public function __construct(Account $creditor, $amount, Entity $owner, $due, $name = false){
		if(!($creditor->getEntity() instanceof Service) and !($creditor->getEntity() instanceof PlayerEnt)){
			throw new \BadMethodCallException("Loan must be provided by a player or a service");
		}
		if(!is_string($name)){
			$name = "Loan from {$creditor->getEntity()->getAbsolutePrefix()} {$creditor->getEntity()->getName()}: {$creditor->getName()}";
		}
		parent::__construct($name, $amount, $owner);
		$this->setIsLiability(true);
		$this->due = $due;
		$this->creditor = $creditor;
	}
	public function toArray(){
		$data = parent::toArray();
		$data["due"] = $this->due;
		$data["creditor"] = $this->creditor->getUniqueName();
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
}
