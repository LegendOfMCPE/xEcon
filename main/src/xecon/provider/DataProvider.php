<?php

namespace xecon\provider;

use xecon\entity\Entity;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\XEcon;

abstract class DataProvider{
	/** @var XEcon */
	private $main;
	public function __construct(XEcon $plugin){
		$this->main = $plugin;
	}
	/**
	 * @return XEcon
	 */
	public function getMain(){
		return $this->main;
	}
	/**
	 * @param Entity $entity
	 */
	public abstract function loadEntity($entity);
	/**
	 * @param Entity $entity
	 */
	public abstract function saveEntity($entity);
	public abstract function deleteEntity($uniqueName);
	/**
	 * @param $ip
	 * @return bool whether the IP is already registered
	 */
	public abstract function touchIP($ip);
	/**
	 * This function is only supposed to be called when {@link Entity::initDefaultAccounts()} is called.
	 * @param PlayerEnt $ent
	 * @return int
	 */
	public function checkPlayer(PlayerEnt $ent){
		$ent->acquire();
		if(!$ent->valid()){
			//			throw new \BadMethodCallException("Trying to check default money for an offline player");
			return -1;
		}
		if(!$this->touchIP($ent->get()->getAddress()) or $isName = $this->getMain()->isGiveForEachName()){
			// touch IP anyways, so don't put touchIP() behind the "or"!
			$this->giveDefault($ent);
			if(!isset($isName)){
				$this->getMain()->getLogger()->info("Registering new IP: {$ent->get()->getAddress()}");
			}
			return 1;
		}
		return 0;
	}
	public function giveDefault(PlayerEnt $ent){
		$this->getMain()->getLogger()->info("Giving default money to $ent");
		$cash = $ent->getAccount(PlayerEnt::ACCOUNT_CASH);
		$bank = $ent->getAccount(PlayerEnt::ACCOUNT_BANK);
		$service = $this->getMain()->getService()->getService(Service::ACCOUNT_OPS);
		$service->pay($cash, $this->getMain()->getDefaultCashMoney(), "Initial cash capital");
		$service->pay($bank, $this->getMain()->getDefaultBankMoney(), "Initial bank capital");
	}
	public function close(){

	}
	public function isAvailable(){
		return true;
	}
}
