<?php

namespace pemapmodder\utils;

class FileUtils{
	// directories //
	public static function copyDir($from, $to){
		if(substr($from, -1) !== "/") $from .= "/";
		if(substr($to, -1) !== "/") $from .= "/";
		if(is_dir($to)) return false;
		if(!is_dir($from)) return false;
		$dir = dir($from);
		while(($fn = $dir->read()) !== false){
			if(is_file($from.$fn))
				copy($from.$fn, $to.$fn);
			elseif(str_replace(array(".", "/"), array("", ""), $fn) !== "" and is_dir($from.$fn))
				self::copyDir($from.$fn, $to.$fn);
		}
		return true;
	}
	public static function delDir($dir){
		if(substr($dir, -1) !== "/") $dir .= "/";
		if(!is_dir($dir)) return false;
		$directory = dir($dir);
		while(($fn = $dir->read()) !== false){
			if(is_file($dir.$fn))
				unlink($dir.$fn);
			elseif(str_replace(array(".", "/"), array("", ""), $fn) !== "" and is_dir($dir.$fn))
				self::delDir($dir);
		}
		rmdir($dir);
		return true;
	}
}
