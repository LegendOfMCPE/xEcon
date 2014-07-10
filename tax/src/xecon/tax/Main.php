<?php

namespace xecon\tax;

use pocketmine\event\Listener;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\plugin\PluginBase;
use xecon\tax\taxes\TaxType;
use xecon\utils\CallbackPluginTask;

class Main extends PluginBase implements Listener{
	private $service;
	/** @var TaxType[] */
	private $types = [];
	private $compiled = false;
	/** @var taxes\Tax[] */
	private $taxes = [];
	public function onEnable(){
		/** @var \xecon\Main $xEcon */
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
		$this->registerTaxType(new TaxType("base", "xecon\\tax\\taxes\\BaseTax", $this));
	}
	public function registerTaxType(TaxType $type){
		$this->types[$type->getName()] = $type;
		if($this->compiled === true){
			$this->onPostRegister();
		}
	}
	public function onPostRegister(){
		$this->taxes = [];
		foreach($this->getConfig()->get("taxes") as $tax){
			$type = $tax["type"];
			if(!isset($this->types[$type])){
				$this->getLogger()->notice("Tax type \"$type\" not found. The tax will not be loaded this time.");
			}
			$this->taxes[] = $this->types[$type]->create($tax);
		}
		$this->getLogger()->info(count($this->taxes)." taxes have been loaded.");
	}
	/**
	 * @return taxes\Tax[]
	 */
	public function getTaxes(){
		return $this->taxes;
	}
	public function onOtherDisable(PluginDisableEvent $event){
		foreach($this->types as $name => $type){
			if($type->getPlugin() === $event->getPlugin()){
				unset($this->types[$name]);
			}
		}
	}
}
