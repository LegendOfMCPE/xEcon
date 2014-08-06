<?php

namespace xecon\provider;

use xecon\account\Loan;
use xecon\entity\Entity;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\Main;

class JSONDataProvider{
	use DataProvider{
		__construct as dp_constructor;
	}
	/** @var string */
	private $path;
	/** @var boolean */
	private $pretty;
	public function __construct(Main $main, array $args){
		$this->dp_constructor($main);
		$this->path = $args["path"];
		$this->pretty = $args["pretty print"];
	}
	public function getPath(Entity $entity){
		$path = $this->getMain()->getDataFolder().str_replace(
			["<type>", "<name>"], [$entity->getAbsolutePrefix(), $entity->getName()], $this->path);
		@mkdir(dirname($path), 0777, true);
		return $path;
	}
	public function loadEntity(Entity $entity){
		$file = $this->getPath($entity);
		if(!is_file($file)){
			$entity->initDefaultAccounts();
			return;
		}
		$data = json_decode(file_get_contents($file));
		foreach($data["accounts"] as $account){
			$entity->addAccount($account["name"], $account["amount"],
				$account["max containable"], $account["min amount"]);
		}
		foreach($data["loans"] as $loan){
			$from = $loan["from"];
			switch($from["type"]){
				case PlayerEnt::ABSOLUTE_PREFIX:
					$from = $this->getMain()->getPlayerEnt($from["name"])->getAccount($from["account"]);
					break;
				case Service::TYPE:
					$from = $this->getMain()->getService()->getService($from["account"]);
					break;
				default:
					throw new \RuntimeException("Unsupported creditor type: ".$from["type"]);
			}
			$entity->addLoanRaw(new Loan($from, $data["amount"], $entity,
				$data["due"], $data["increase per hour"], $data["name"],
				$data["creation"], $data["original amount"], $data["last interest update"]));
		}
	}
	public function saveEntity(Entity $entity){
		$file = $this->getPath($entity);
		$data = ["accounts" => [], "loans" => []];
		foreach($entity->getAccounts() as $account){
			$data["accounts"][$account->getName()] = [
				"name" => $account->getName(),
				"amount" => $account->getAmount(),
				"max containable" => $account->getMaxContainable(),
				"min amount" => $account->getMinAmount()
			];
		}
		foreach($entity->getLoans() as $loan){
			$data["loans"][$loan->getName()] = [
				"name" => $loan->getName(),
				"amount" => $loan->getAmount(),
				"due" => $loan->getDue(),
				"increase per hour" => $loan->getIncreasePerHour(),
				"creation" => $loan->getCreation(),
				"original amount" => $loan->getOriginalAmount(),
				"last interest update" => $loan->getLastInterestUpdate(),
				"from" => [
					"type" => $loan->getCreditor()->getEntity()->getAbsolutePrefix(),
					"name" => $loan->getCreditor()->getEntity()->getName(),
					"account" => $loan->getName()
				]
			];
		}
		file_put_contents($file, json_encode($data), ($this->pretty ? JSON_PRETTY_PRINT:0) | JSON_BIGINT_AS_STRING);
	}
	public function deleteEntity($name){
		$path = str_replace(["<type>", "<name>"], explode("/", $name), $this->path);
		return @unlink($this->getMain()->getDataFolder().$path);
	}
}
