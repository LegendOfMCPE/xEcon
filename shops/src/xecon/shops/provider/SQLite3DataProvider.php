<?php

namespace xecon\shops\provider;

use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Server;
use xecon\Main;
use xecon\shops\shops\PhysicalShop;
use xecon\shops\Shops;
use xecon\shops\shops\Shop;

class SQLite3DataProvider implements DataProvider{
	/** @var Server */
	private $server;
	/** @var Shops */
	private $shops;
	/** @var Main  */
	private $main;
	/** @var \SQLite3 */
	private $db;
	public function __construct(Server $server, Shops $shops, Main $main, array $args){
		$this->server = $server;
		$this->shops = $shops;
		$this->main = $main;
		$this->path = $args["path"];
		$this->db = new \SQLite3($this->shops->getDataFolder().$this->path);
		$this->db->exec("CREATE TABLE IF NOT EXISTS xecon_shops (
				id INTEGER PRIMARY KEY,
				item_id INTEGER UNSIGNED,
				item_damage INTEGER,
				metadata INTEGER UNSIGNED,
				amount INTEGER UNSIGNED,
				price DOUBLE UNSIGNED,
				type INTEGER
				);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS xecon_shops_coords (
				id INTEGER PRIMARY KEY,
				x INTEGER,
				y INTEGER,
				z INTEGER,
				level TEXT
				);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS xecon_shops_misc (name TEXT, value TEXT);");
		$this->db->exec("INSERT INTO xecon_shops_misc VALUES ('nextid', 0);");
	}
	public function getShopByPosition(Position $pos){
		$level = $this->db->escapeString($pos->getLevel()->getName());
		$result = $this->db->query("SELECT id FROM xecon_shops_coords WHERE x = {$pos->x} AND y = {$pos->y} and z = {$pos->z} and level = '$level';");
		$result = $result->fetchArray(SQLITE3_ASSOC);
		if(!is_array($result)){
			return null;
		}
		$id = $result["id"];
		$data = $this->db->query("SELECT * FROM xecon_shops WHERE id = $id;")->fetchArray(SQLITE3_ASSOC);
		return new PhysicalShop($pos->x, $pos->y, $pos->z, $pos->getLevel(), $this->shops,
			Item::get($data["item_id"], $data["item_damage"]), $data["metadata"] & 0b10 !== 0,
			$data["amount"], $data["price"], $data["metadata"] & 0b01 !== 0, $id);
	}
	public function getShopByID($id){
		$data = $this->db->query("SELECT * FROM xecon_shops WHERE id = $id;")->fetchArray(SQLITE3_ASSOC);
		if(!is_array($data)){
			return null;
		}
		switch($data["type"]){
			case Shops::TYPE_PHYSICAL:
				$dat = $this->db->query("SELECT * FROM xecon_shops_coords WHERE id = $id;");
				if(!$this->server->isLevelLoaded($dat["level"])){
					if(!$this->server->isLevelGenerated($dat["level"])){
						return null;
					}
					$this->server->loadLevel($dat["level"]);
				}
				$level = $this->server->getLevelByName($dat["level"]);
				return new PhysicalShop($dat["x"], $dat["y"], $dat["z"], $level, $this->shops,
					Item::get($data["item_id"], $data["item_damage"]), $data["metadata"] & 0b10 !== 0,
					$data["amount"], $data["price"], $data["metadata"] & 0b01 !== 0, $id);
		}
		return null;
	}
	public function addShop(Shop $shop){
		if($shop instanceof PhysicalShop){
			$type = Shops::TYPE_PHYSICAL;
			$this->db->exec("INSERT OR REPLACE INTO xecon_shops VALUES (
					{$shop->getID()},
					{$shop->getItem()->getID()},
					{$shop->getItem()->getDamage()},
					{$shop->getMetadata()},
					{$shop->getAmount()},
					{$shop->getPrice()}
					$type
					);");
			$this->db->exec("INSERT OR REPLACE INTO xecon_shops_coords VALUES (
					{$shop->getID()},
					{$shop->x},
					{$shop->y},
					{$shop->z},
					{$shop->getLevel()->getName()}
					);");
		}
		else{
			throw new \ErrorException("Unsupported shop type: ".get_class($shop));
		}
	}
	public function rmShop(Shop $shop){
		$this->db->query("DELETE FROM xecon_shops WHERE id = {$shop->getID()};");
		$this->db->query("DELETE FROM xecon_shops_coords WHERE id = {$shop->getID()};");
	}
	public function isAvailable(){
		return true;
	}
	public function close(){
		$this->db->close();
	}
	public function nextID(){
		$value = $this->db->query("SELECT value FROM xecon_shops_misc WHERE name = 'nextid';")->fetchArray(SQLITE3_ASSOC)["value"];
		$added = intval($value) + 1;
		$this->db->exec("UPDATE xecon_shops_misc SET value = '$added' WHERE name = 'nextid';");
		return $value;
	}
}
