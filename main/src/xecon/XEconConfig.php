<?php

namespace xecon;

class XEconConfig{
	/** @var number */
	private $defaultBank, $defaultCash, $maxBank, $maxCash, $maxOverdraft;
	/** @var bool */
	private $defaultForIps;
	/** @var array */
	private $uniMysqlDetails, $dataProvider, $logs;
	/** @var bool */
	private $reportEnabled;
	/** @var string */
	private $reportHost;
	/** @var int */
	private $reportTimeout;
	public function __construct(array $config){
		$player = $config["player account"];
		$default = $player["default"];
		$this->defaultBank = $default["bank"];
		$this->defaultCash = $default["cash"];
		$max = $player["max"];
		$this->maxBank = $max["bank"];
		$this->maxCash = $max["cash"];
		$this->maxOverdraft = $player["bank"]["overdraft"];
		$this->defaultForIps = $default["give for each ip"];
		$this->uniMysqlDetails = $config["universal mysqli database"]["connection details"];
		$this->dataProvider = $config["data provider"];
		$this->logs = $config["logs"];
		$report = $config["report errors"];
		$this->reportEnabled = $report["enabled"];
		$this->reportHost = $report["host"];
		$this->reportTimeout = $report["timeout"];
	}
	/**
	 * @return mixed
	 */
	public function getDefaultBank(){
		return $this->defaultBank;
	}
	/**
	 * @return number
	 */
	public function getDefaultCash(){
		return $this->defaultCash;
	}
	/**
	 * @return number
	 */
	public function getMaxBank(){
		return $this->maxBank;
	}
	/**
	 * @return number
	 */
	public function getMaxCash(){
		return $this->maxCash;
	}
	/**
	 * @return number
	 */
	public function getMaxOverdraft(){
		return $this->maxOverdraft;
	}
	/**
	 * @return boolean
	 */
	public function isDefaultForIps(){
		return $this->defaultForIps;
	}
	/**
	 * @return array
	 */
	public function getUniMysqlDetails(){
		return $this->uniMysqlDetails;
	}
	/**
	 * @return array
	 */
	public function getDataProviderOpts(){
		return $this->dataProvider;
	}
	/**
	 * @return array
	 */
	public function getLogsOpts(){
		return $this->logs;
	}
	/**
	 * @return boolean
	 */
	public function isReportEnabled(){
		return $this->reportEnabled;
	}
	/**
	 * @return string
	 */
	public function getReportHost(){
		return $this->reportHost;
	}
	/**
	 * @return mixed
	 */
	public function getReportTimeout(){
		return $this->reportTimeout;
	}
}
