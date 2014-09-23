<?php

namespace xecon\provider;

use xecon\entity\Entity;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\XEcon;

abstract class DataProvider{
	/** @var XEcon */
	private $main;
	public function __construct(XEcon $main){
		$this->main = $main;
	}
	/**
	 * @return XEcon
	 */
	public function getMain(){
		return $this->main;
	}
	public abstract function loadEntity(Entity $entity);
	public abstract function saveEntity(Entity $entity);
	public abstract function deleteEntity($uniqueName);
	/**
	 * @param $ip
	 * @return bool whether the IP is already registered
	 */
	public abstract function touchIP($ip);
	public function checkPlayer(PlayerEnt $ent){
		$ent->acquire();
		if(!$ent->valid()){
//			throw new \BadMethodCallException("Trying to check default money for an offline player");
			return -1;
		}
		else{
			if(!$this->touchIP($ent->get()->getAddress()) or $this->getMain()->isGiveForEachName()){
				// touch IP anyways, so don't put touchIP() behind the "or"!
				$this->giveDefault($ent);
				return 1;
			}
			else{
				return 0;
			}
		}
	}
	public function giveDefault(PlayerEnt $ent){
		$cash = $ent->getAccount(PlayerEnt::ACCOUNT_CASH);
		$bank = $ent->getAccount(PlayerEnt::ACCOUNT_BANK);
		$service = $this->getMain()->getService()->getService(Service::ACCOUNT_OPS);
		$service->pay($cash, $this->getMain()->getDefaultCashMoney(), "Initial capital");
		$service->pay($bank, $this->getMain()->getDefaultBankMoney(), "Initial capital");
	}
	public function close(){

	}
	public function isAvailable(){
		return true;
	}
}
