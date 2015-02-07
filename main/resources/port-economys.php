<?php
$opts = getopt("t:i::");
if(!isset($opts["t"])){
	die("Usage: " . PHP_BINARY . " " . basename(__FILE__) . " -t<database type> [-i<input economys folder>]");
}
$type = $opts["t"];
$input = "../EconomysAPI";
if(isset($opts["i"])){
	$input = $opts["i"];
}
if(!is_dir($input)){
	die("Invalid input file: $input." . PHP_EOL . "Specify the input folder by -i<input folder>");
}
$input = normalizePath($input);
switch($type){
	default:
		die(<<<EOU
Unsupported database type. Supported types:
* disk - accounts saved in JSON files and registered IPs saved in a LIST file in the plugin directory
* sqlite3 - accounts and registered IPs saved in a SQLite3 database file in the plugin directory
* mysqli - accounts and registered IPs saved in an external MySQL database (can be at localhost)
EOU
		);
		break;
	case "disk":
		$opts = getopt("o::");
		$out = ".";
		if(isset($opts["o"])){
			$out = $opts["o"];
		}
		if(is_file($out)){
			die("Output folder is a file.");
		}
		$out = normalizePath($out);
		@mkdir($out, 0777, true);

}

function normalizePath($input){
	return rtrim(realpath($input), "\\/") . "/";
}
