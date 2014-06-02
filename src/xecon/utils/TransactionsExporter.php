<?php

namespace xecon\utils;

use pocketmine\scheduler\AsyncTask;

abstract class TransactionsExporter extends AsyncTask{
	public static function exportReport($dir, $target){
		$task = new static($dir);
		Server::getInstance()->getScheduler()->scheduleAsyncTask($task);
	}
	public function __construct($dir, $target){
		$this->dir = $dir;
		$this->target = $target;
	}
	public function onRun(){
		$dir = $this->dir;
		$targ = $this->target;
		console("[INFO] Exporting $dir accounts...");
		$sheets = array();
		foreach(scandir($dir) as $file){
			if(!is_file($file)){
				continue;
			}
			$lines = json_decode(file_get_contents("$dir/$file"));
			$sheet = substr($file, 0, -4);
			$sheets[$sheet] = array();
			foreach($lines as $line){
				$pieces = explode("|", $line);
				// [timestamp, details, amount]
				$sheets[$sheet][] = array("Date and time", "__DATE__".str_replace("+", ".", date(DATE_ATOM, $pieces[0])));
				$sheets[$sheet][] = array("Amount", "\$".$pieces[2]);
				$sheets[$sheet][] = array("Details", $pieces[1]);
			}
		}
		$this->setResult($targ);
	}
}
