<?php

namespace xecon\provider;

use xecon\account\Loan;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\XEcon;

class MysqliDataProvider extends DataProvider{
	/** @var \mysqli */
	private $db;
	private $mtn, $atn, $ltn, $itn;
	/** @var bool */
	private $universal;
	public function __construct(XEcon $plugin, \mysqli $db, array $args){
		parent::__construct($plugin);
		$this->db = $db;
		$prefix = $args["table name prefix"];
		$this->mtn = $prefix . "ent_index";
		$this->atn = $prefix . "ent_accounts";
		$this->ltn = $prefix . "ent_loans";
		$this->itn = $prefix . "registered_ips";
		$this->universal = $args["use universal"];
		$db->query("CREATE TABLE IF NOT EXISTS {$this->mtn} (
				ent_type VARCHAR(255),
				ent_name VARCHAR(255),
				register_time BIGINT UNSIGNED,
				last_modify BIGINT UNSIGNED
				);");
		$db->query("CREATE TABLE IF NOT EXISTS {$this->atn} (
				ent_type VARCHAR(255),
				ent_name VARCHAR(255),
				name VARCHAR(255),
				amount DOUBLE SIGNED,
				max_containable BIGINT UNSIGNED,
				min_amount INT SIGNED
				);");
		$db->query("CREATE TABLE IF NOT EXISTS {$this->ltn} (
				ent_type VARCHAR(255),
				ent_name VARCHAR(255),
				name VARCHAR(255),
				amount DOUBLE SIGNED,
				due BIGINT UNSIGNED,
				increase_per_hour DOUBLE SIGNED,
				creation BIGINT UNSIGNED,
				original_amount DOUBLE SIGNED,
				last_interest_update BIGINT UNSIGNED,
				from_type VARCHAR(255),
				from_name VARCHAR(255),
				from_account VARCHAR(255)
				);");
		$db->query("CREATE TABLE IF NOT EXISTS {$this->itn} (
				ip VARBINARY(4) PRIMARY KEY
				);");
	}
	public function loadEntity($entity, $create = true){
		$result = $this->db->query("SELECT * FROM {$this->mtn} WHERE
				ent_type = '{$this->db->escape_string($entity->getAbsolutePrefix())}' AND
				ent_name = '{$this->db->escape_string($entity->getName())}';");
		$data = $result->fetch_assoc();
		$result->close();
		if(!is_array($data)){
			if(!$create){
				EntityNotCreatedException::throwEx();
			}
			$entity->initDefaultAccounts();
			return;
		}
		$result = $this->db->query("SELECT * FROM {$this->atn} WHERE
				ent_type = '{$this->db->escape_string($entity->getAbsolutePrefix())}' AND
				ent_name = '{$this->db->escape_string($entity->getName())}';");
		while(is_array($data = $result->fetch_assoc())){
			$entity->addAccount($data["name"], $data["amount"], $data["max_containable"], $data["min_amount"]);
		}
		$result->close();
		$result = $this->db->query("SELECT * FROM {$this->ltn} WHERE
				ent_type = '{$this->db->escape_string($entity->getAbsolutePrefix())}' AND
				ent_name = '{$this->db->escape_string($entity->getName())}';");
		while(is_array($data = $result->fetch_assoc())){
			switch($data["from_type"]){
				case PlayerEnt::ABSOLUTE_PREFIX:
					$from = $this->getMain()->getPlayerEnt($data["from_name"])->getAccount($data["from_account"]);
					break;
				case Service::TYPE:
					if($data["from_name"] === Service::NAME){
						$from = $this->getMain()->getService()->getService($data["from_account"]);
						break;
					}
				default:
					throw new \RuntimeException("Unsupported creditor type: " . $data["from_type"]);
			}
			$entity->addLoanRaw(new Loan($from, $data["amount"], $entity, $data["due"],
				$data["increase_per_hour"], $data["name"], $data["creation"],
				$data["original_amount"], $data["last_interest_update"]));
		}
		$result->close();
	}
	public function saveEntity($entity){
		$result = $this->db->query("SELECT * FROM {$this->mtn} WHERE
				ent_type = '{$this->db->escape_string($entity->getAbsolutePrefix())}' AND
				ent_name = '{$this->db->escape_string($entity->getName())}';");
		$data = $result->fetch_assoc();
		$result->close();
		if(!(is_array($data))){
			$this->db->query("INSERT INTO {$this->mtn} VALUES (
					'{$this->db->escape_string($entity->getAbsolutePrefix())}',
					'{$this->db->escape_string($entity->getName())}',
					UNIX_TIMESTAMP(),
					UNIX_TIMESTAMP()
					);");
		}
		else{
			$this->db->query("UPDATE {$this->mtn} SET last_modify=UNIX_TIMESTAMP() WHERE
					ent_type = '{$this->db->escape_string($entity->getAbsolutePrefix())}' AND
					ent_name = '{$this->db->escape_string($entity->getName())}';");
		}
		$this->db->query("DELETE FROM {$this->atn} WHERE
					ent_type = '{$this->db->escape_string($entity->getAbsolutePrefix())}' AND
					ent_name = '{$this->db->escape_string($entity->getName())}';");
		foreach($entity->getAccounts() as $acc){
			$this->db->query("INSERT INTO {$this->atn} VALUES (
					'{$this->db->escape_string($entity->getAbsolutePrefix())}',
					'{$this->db->escape_string($entity->getName())}',
					'{$this->db->escape_string($acc->getName())}',
					{$acc->getAmount()},
					{$acc->getMaxContainable()},
					{$acc->getMinAmount()}
					);");
		}
		$this->db->query("DELETE FROM {$this->ltn} WHERE
					ent_type = '{$this->db->escape_string($entity->getAbsolutePrefix())}' AND
					ent_name = '{$this->db->escape_string($entity->getName())}';");
		foreach($entity->getLoans() as $loan){
			$this->db->query("INSERT INTO {$this->ltn} VALUES (
					'{$this->db->escape_string($entity->getAbsolutePrefix())}',
					'{$this->db->escape_string($entity->getName())}',
					'{$this->db->escape_string($loan->getName())}',
					{$loan->getAmount()},
					{$loan->getDue()},
					{$loan->getHourlyIncrease()},
					{$loan->getCreationTime()},
					{$loan->getOriginalAmount()},
					{$loan->getLastInterestUpdate()},
					'{$this->db->escape_string($loan->getCreditor()->getEntity()->getAbsolutePrefix())}',
					'{$this->db->escape_string($loan->getCreditor()->getEntity()->getName())}',
					'{$this->db->escape_string($loan->getCreditor()->getName())}'
					);");
		}
	}
	public function deleteEntity($uniqueName){
		list($type, $name) = explode("/", $uniqueName);
		$this->db->query(<<<EOQ
DELETE FROM {$this->atn} WHERE ent_type = '$type' AND ent_name = '$name';
DELETE FROM {$this->ltn} WHERE ent_type = '$type' AND ent_name = '$name';
EOQ
);
	}
	public function touchIP($ip){
		$ip = $this->db->escape_string(implode("", array_map(function($token){
			return chr(intval($token));
		}, explode(".", $ip))));
		$result = $this->db->query("SELECT ip FROM {$this->itn} WHERE ip = '$ip';");
		$exists = is_array($result->fetch_assoc());
		$result->close();
		if(!$exists){
			$this->db->query("INSERT INTO {$this->itn} VALUES ('$ip');");
		}
		return $exists;
	}
	public function isAvailable(){
		return $this->db->ping();
	}
	public function close(){
		if(!$this->universal){
			$this->db->close();
		}
	}
	/**
	 * @return bool
	 */
	public function isUniversal(){
		return $this->universal;
	}
}
