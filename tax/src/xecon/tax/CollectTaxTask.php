<?php

namespace xecon\tax;

use pocketmine\scheduler\PluginTask;

class CollectTaxTask extends PluginTask{
	private $cnt = 0;
	public function onRun($t){
		$this->cnt++;
		/** @var TaxPlugin $main */
		$main = $this->getOwner();
		foreach($main->getTaxWrappers() as $wrapper){
			$interval = $wrapper->getFrequency() / $main->getFrequencyHCF();
			$cnt = $this->cnt;
			while($cnt >= $interval){
				$cnt -= $interval;
			}
			if($cnt === 0){
				$wrapper->iterate();
			}
		}
	}
}
