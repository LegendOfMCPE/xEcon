<?php

namespace xecon\tax;

use pocketmine\event\Listener;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\plugin\PluginBase;
use xecon\tax\tax\Tax;
use xecon\utils\CallbackPluginTask;

class TaxPlugin extends PluginBase implements Listener{
	/** @var \xecon\account\Account */
	private $service;
	/** @var Tax[] */
	private $taxes = [];
	public function onEnable(){
		/** @var \xecon\XEcon $xEcon */
		$xEcon = $this->getServer()->getPluginManager()->getPlugin("xEcon");
		$xEcon->getService()->registerService("TaxColl");
		$this->service = $xEcon->getService()->getService("TaxColl");
		$this->saveDefaultConfig();
		$freq = floatval($this->getConfig()->get("frequency"));
		switch($u = $this->getConfig()->get("unit")){
			case "hour":
			case "hours":
				$freq *= 60;
			case "minute":
			case "minutes":
				$freq *= 60;
			case "second":
			case "seconds":
				$freq *= 20;
				break;
			default:
				trigger_error("xEcon tax config unidentified unit: \"$u\". The default value (minute) will be used.", E_USER_WARNING);
				$freq *= 1200;
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CollectTaxTask($this), $freq, $freq);
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackPluginTask($this, array($this, "onPostRegister")), 1); // give other plugins the chance to register
	}
	public function registerTaxType(Tax $type){
		$this->taxes[$type->getName()] = $type;
	}
	public function onPostRegister(){
		foreach($this->getConfig()->get("taxes") as $tax){
			$type = $tax["type"];
			if(!isset($this->taxes[$type])){
				$this->getLogger()->notice("Tax type \"$type\" not found. The tax will not be loaded this time.");
			}
			$this->taxes[] = $this->taxes[$type]->init($tax);
		}
		$this->getLogger()->info(count($this->taxes)." taxes have been loaded.");
	}
	/**
	 * @return tax\Tax[]
	 */
	public function getTaxes(){
		return $this->taxes;
	}
	public function onOtherDisable(PluginDisableEvent $event){
	}
	/**
	 * @return \xecon\account\Account
	 */
	public function getService(){
		return $this->service;
	}
}
