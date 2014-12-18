<?php

namespace xecon\cmd;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use xecon\utils\ExceptionReportTask;
use xecon\XEcon;

abstract class XEconCommand extends Command implements PluginIdentifiableCommand{
	private $main;
	public function __construct(XEcon $main){
		$a = $this->getAliases_();
		if(!is_array($a)){
			$a = [$a];
		}
		parent::__construct($this->getName_(), $this->getDesc_(), $this->getUsage_(), $a);
		$this->main = $main;
	}
	protected abstract function getName_();
	protected abstract function getDesc_();
	protected abstract function getUsage_();
	protected function getAliases_(){
		return [];
	}
	public function getPlugin(){
		return $this->main;
	}
	public function execute(CommandSender $sender, $alias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		try{
			if(($r = $this->execute_($sender, $args)) === false){
				$sender->sendMessage($this->getUsage_());
				return false;
			}
			if(is_string($r)){
				$sender->sendMessage($r);
			}
		}
		catch(\Exception $e){
			$sender->sendMessage("Unfortunately, an error was caught while executing your command. The error has been reported to console.");
			$this->getPlugin()->getLogger()->error("An Exception was caught during executing command {$this->getName_()}:");
			$this->getPlugin()->getLogger()->error(get_class($e) . ": " . $e->getMessage());
			if($this->getPlugin()->getXEconConfiguration()->isReportEnabled()){
				$this->getPlugin()->getLogger()->alert("Posting exception report to report host in a separate thread...");
				$this->getPlugin()->getServer()->getScheduler()->scheduleAsyncTask(new ExceptionReportTask(
					$this->getPlugin()->getXEconConfiguration()->getReportHost(),
					$this->getPlugin(), $e, [
						"class" => "command",
						"name" => $this->getName_(),
						"args" => $args,
						"sender" => [
							"class" => get_class($sender),
							"name" => $sender->getName()
						],
						"label" => $alias
					], 5
				));
			}
		}
		return true;
	}
	public abstract function execute_(CommandSender $sender, array $args);
}
