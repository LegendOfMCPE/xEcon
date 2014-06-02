<?php
/**
 * Created by PhpStorm.
 * User: 15INCH
 * Date: 14年6月3日
 * Time: 上午12:00
 */

namespace xecon\utils;

use xecon\entity\Entity;

class FolderDatabase implements \ArrayAccess{
	private $path;
	public function __cosntruct($path){
		$this->path = $path;
	}
	public function offsetExists($k){
		return is_dir($this->path."$k/");
	}
	public function offsetGet($k){
		return Entity::loadFromDir($this->path."$k/");
	}
	public function offsetSet($k, $v){
		if(!($v instanceof Entity)){
			trigger_error("Unexpected argument 2 data type passed, xecon\\entity\\Entity expected", E_USER_ERROR);
			return;
		}
		@mkdir($this->path."$k/");
		$v->saveToDir($this->path."$k/");
	}
	public function offsetUnset($k){
		FileUtils::delDir($this->path."$k/");
	}
	public function getPath(){
		return $this->path;
	}
}
