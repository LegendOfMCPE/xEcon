<?php

namespace xecon\shops;

use pocketmine\plugin\PluginBase;
use xecon\shops\provider\SQLite3DataProvider;

class Shops extends PluginBase{
	const SERVICE_NAME = "Shops";
	const TYPE_PHYSICAL = 1;
	/** @var \xecon\XEcon */
	private $xEcon;
	/** @var \xecon\shops\provider\DataProvider */
	private $provider;
	public function onEnable(){
		$this->connectMain();
		$this->xEcon->getService()->registerService(self::SERVICE_NAME);
		$this->saveDefaultConfig();
		$provider = $this->getConfig()->get("data provider");
		$type = $provider["type"];
		$args = $provider[$type];
		switch(strtoupper($type)){
			case "SQLITE3":
				$this->provider = new SQLite3DataProvider($this->getServer(), $this, $this->xEcon, $args);
				break;
		}
	}
	private function connectMain(){
		$this->xEcon = $this->getServer()->getPluginManager()->getPlugin("xEcon");
	}
	public function getService(){
		return $this->xEcon->getService()->getService(self::SERVICE_NAME);
	}
	/**
	 * @return provider\DataProvider
	 */
	public function getProvider(){
		return $this->provider;
	}
}
