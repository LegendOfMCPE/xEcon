<?php

namespace xecon\provider;

use pocketmine\utils\Config;
use xecon\account\Loan;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\XEcon;

class JSONDataProvider extends DataProvider{
	/** @var string */
	private $path;
	/** @var boolean */
	private $pretty;
	/** @var Config */
	private $ipList;
	public function __construct(XEcon $plugin, array $args){
		parent::__construct($plugin);
		$this->path = $plugin->getDataFolder() . $args["entities path"];
		$this->ipList = new Config($plugin->getDataFolder() . $args["list path"], Config::ENUM);
		$this->pretty = $args["pretty print"];
	}
	/**
	 * @param \xecon\entity\Entity $entity
	 * @return string
	 */
	public function getPath($entity){
		$path = str_replace(["<type>", "<name>"], [$entity->getAbsolutePrefix(), $entity->getName()], $this->path);
		@mkdir(dirname($path), 0777, true);
		return $path;
	}
	public function loadEntity($entity){
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
					if($from["name"] === Service::NAME){
						$from = $this->getMain()->getService()->getService($from["account"]);
						break;
					}
				default:
					throw new \RuntimeException("Unsupported creditor type: " . $from["type"]);
			}
			$entity->addLoanRaw(new Loan($from, $data["amount"], $entity,
				$data["due"], $data["increase per hour"], $data["name"],
				$data["creation"], $data["original amount"], $data["last interest update"]));
		}
	}
	public function saveEntity($entity){
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
				"increase per hour" => $loan->getHourlyIncrease(),
				"creation" => $loan->getCreationTime(),
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
		return @unlink($this->getMain()->getDataFolder() . $path);
	}
	public function touchIP($ip){
		if(!$this->ipList->exists($ip)){
			$this->ipList->set($ip);
			return false;
		}
		return true;
	}
}
