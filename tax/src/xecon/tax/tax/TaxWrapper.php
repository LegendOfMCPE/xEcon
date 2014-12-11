<?php

namespace xecon\tax\tax;

use xecon\tax\TaxPlugin;

class TaxWrapper{
	/** @var TaxPlugin */
	private $plugin;
	/** @var string */
	private $name;
	/** @var Tax */
	private $tax;
	/** @var int */
	private $frequency;
	/** @var string[] */
	private $sourceAccounts;
	/** @var TaxExemption */
	private $exemptions;
	public function __construct(TaxPlugin $plugin, array $args){
		$this->plugin = $plugin;
		$this->name = $args["name"];
		$type = $args["type"];
		if(substr($type, 0, 1) === "\\"){
			$type = substr($type, 1);
		}
		else{
			$type = "xecon\\tax\\tax\\$type";
		}
		try{
			if(!class_exists($type, true)){
				throw new \RuntimeException("cannot load class $type");
			}
			$class = new \ReflectionClass($type);
			if(!$class->implementsInterface("xecon\\tax\\Tax")){
				throw new \RuntimeException("$type doesn't implement interface xecon\\tax\\Tax");
			}
			/** @var Tax $tax */
			$tax = $class->newInstance($args, $this);
			$this->tax = $tax;
		}
		catch(\Exception $e){
			$this->plugin->getLogger()->error($msg = "Cannot load tax $this->name: {$e->getMessage()}");
			throw new \Exception($msg, 0, $e);
		}
		list($this->frequency, $unit) = $args["frequency"];
		switch($unit){
			case "hr":
				$this->frequency *= 60;
			case "min":
				$this->frequency *= 60;
			case "sec":
				$this->frequency *= 20;
			case "ticks":
				break;
			default:
				$this->frequency *= 1200;
				$this->plugin->getLogger()->warning("Unknown unit $unit; assumed as 'min'.");
				break;
		}
		$this->sourceAccounts = $args["source account"];
		$this->exemptions = isset($args["exemptions"]) ? new TaxExemption($this->plugin, $args["exemptions"]):new ConstTaxExemption($this->plugin, false);
	}
	/**
	 * @return TaxPlugin
	 */
	public function getPlugin(){
		return $this->plugin;
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @return Tax
	 */
	public function getTaxObject(){
		return $this->tax;
	}
	/**
	 * @return int
	 */
	public function getFrequency(){
		return $this->frequency;
	}
	/**
	 * @return string[]
	 */
	public function getSourceAccounts(){
		return $this->sourceAccounts;
	}
	public function iterate(){
		foreach($this->plugin->getXEcon()->getPlayerEnts() as $ent){
			if(!$this->exemptions->isExempted($ent->getPlayer(), $ent)){
				$this->tax->execute($ent);
			}
		}
	}
}
