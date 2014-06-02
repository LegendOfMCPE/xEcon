<?php

namespace xecon;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use xecon\utils\FolderDatabase;

class Main extends PluginBase implements Listener{
	/** @var  string directory where economic entity information is stored` */
	private $edir;
	/** @var  FolderDatabase $db */
	private $db;
	public function onEnable(){
		$this->mkdirs();
		$this->db = new FolderDatabase($this->edir);
	}
	private function mkdirs(){
		@mkdir($this->getDataFolder());
		@mkdir($this->edir = $this->getDataFolder()."entities database/");
	}
}
