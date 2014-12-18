<?php

namespace xecon\utils;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ExceptionReportTask extends AsyncTask{
	private $host;
	private $plugin;
	private $payload;
	private $timeout;
	public function __construct($host, Plugin $plugin, \Exception $e, $event, $timeout){
		$this->host = $host;
		$this->plugin = $plugin;
		$this->prepareData($plugin, $e, $event);
		$this->timeout = $timeout;
	}
	private function prepareData(Plugin $plugin, \Exception $e, $event){
		$desc = $plugin->getDescription();
		$sha = false;
		$class = new \ReflectionClass($plugin);
		$file = $class->getProperty("file");
		$file->setAccessible(true);
		$path = $file->getValue($plugin);
		$file->setAccessible(false);
		$path = realpath("$path/.git/refs/heads/master");
		if(is_file($path)){
			$sha = trim(file_get_contents($path));
		}
		$this->payload = serialize([
			"repo" => "LegendOfMCPE/xEcon",
			"plugin" => [
				"name" => $desc->getName(),
				"version" => $desc->getVersion(),
			],
			"event" => $event,
			"exception" => [
				"class" => get_class($e),
				"message" => $e->getMessage(),
				"trace" => $e->getTraceAsString(),
				"trace-array" => $e->getTrace(),
				"file" => $e->getFile(),
				"line" => $e->getLine(),
				"code" => $e->getCode()
			],
			"sha" => $sha
		]);
	}
	public function onRun(){
		$ch = curl_init($this->host);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 PocketMine-MP xEcon",
			"Report: $this->payload"
		]);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int) $this->timeout);
		$this->setResult(curl_exec($ch));
		curl_close($ch);
	}
	public function onCompletion(Server $server){
		if(substr($this->getResult(), 0, 6) !== "200 OK"){
			$this->plugin->getLogger()->alert("The following response message was received when posting an exception report to " . TextFormat::ITALIC . TextFormat::LIGHT_PURPLE . $this->host);
		}
		else{
			$this->plugin->getLogger()->alert("An exception report has been sent to " . TextFormat::ITALIC . TextFormat::LIGHT_PURPLE . $this->host);
		}
	}
}
