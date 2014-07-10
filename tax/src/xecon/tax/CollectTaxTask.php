<?php

namespace xecon\tax;

use pocketmine\scheduler\PluginTask;
use xecon\entity\Entity;

class CollectTaxTask extends PluginTask{
	public function onRun($t){
		/** @var \xecon\Main $xEcon */
		$xEcon = $this->getOwner()->getServer()->getPluginManager()->getPlugin("xEcon"); // this task won't be called if xEcon is disabled, as heard and remembered
		$players = $this->getOwner()->getServer()->getOnlinePlayers();
		foreach($players as $player){
			/** @var \xecon\entity\Entity $ent */
			$ent = $xEcon->getSession($player)->getEntity();
			/** @var Main $xEconTax */
			$xEconTax = $this->getOwner();
			foreach($xEconTax->getTaxes() as $tax){
				$tax->execute($player, $ent);
			}
		}
	}
}
