<?php

namespace xecon\tax;

use pocketmine\event\Listener;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\plugin\PluginBase;
use xecon\tax\tax\TaxWrapper;

class TaxPlugin extends PluginBase implements Listener{
	/** @var \xecon\account\Account */
	private $service;
	/** @var TaxWrapper[] */
	private $taxWrappers = [];
	/** @var \xecon\XEcon */
	private $xEcon;
	private $freq;
	/** @var ExemptionCommandManager */
	private $cvMgr;
	public function onEnable(){
		$this->xEcon = $this->getServer()->getPluginManager()->getPlugin("xEcon");
		$this->xEcon->getService()->registerService("TaxColl");
		$this->service = $this->xEcon->getService()->getService("TaxColl");
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadTaxes();
		$this->freq = self::hcf_array(array_map(function(TaxWrapper $wrapper){
			return $wrapper->getFrequency();
		}, $this->taxWrappers));
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CollectTaxTask($this), $this->freq, $this->freq);
		$this->cvMgr = new ExemptionCommandManager($this);
	}
	private function loadTaxes(){
		foreach($this->getConfig()->get("taxes") as $tax){
			$wrapper = new TaxWrapper($this, $tax);
			$this->taxWrappers[$wrapper->getName()] = $wrapper;
		}
	}
	/**
	 * @return tax\TaxWrapper[]
	 */
	public function getTaxWrappers(){
		return $this->taxWrappers;
	}
	public function onOtherDisable(PluginDisableEvent $event){
	}
	/**
	 * @return \xecon\account\Account
	 */
	public function getService(){
		return $this->service;
	}
	public function getXEcon(){
		return $this->xEcon;
	}
	public function getFrequencyHCF(){
		return $this->freq;
	}
	// math lib. modified from: http://me.dt.in.th/2010/01/hcf-function-in-php/
	public static function hcf($a, $b) {
		if($b > $a){
			return self::hcf($b, $a);
		}
		if($b == 0){
			return $a;
		}
		return self::hcf($b, $a % $b);
	}
	public static function hcf_array($array) {
		if(count($array) < 1){
			return false;
		}
		if(count($array) === 1){
			return $array[0];
		}
		array_unshift($array, self::hcf(array_shift($array), array_shift($array)));
		return self::hcf_array($array);
	}
	public function getCommandValueManager(){
		return $this->cvMgr;
	}
}
