<?php

namespace xecon\shops\provider;

use pocketmine\level\Position;
use pocketmine\Server;
use xecon\XEcon as XEcon;
use xecon\shops\Shops;
use xecon\shops\shops\Shop;

interface DataProvider{
	public function __construct(Server $server, Shops $main, XEcon $xEcon, array $args);
	/**
	 * Gets a shop using the shop's position, returning the shop object if exists, null otherwise
	 *
	 * @param Position $position
	 * @return Shop|null
	 */
	public function getShopByPosition(Position $position);
	/**
	 * Gets a shop using the shop's ID, returning the shop object if exists, null otherwise
	 *
	 * @param int $id
	 * @return Shop|null
	 */
	public function getShopByID($id);
	/**
	 * Adds a shop to the database
	 *
	 * @param Shop $shop
	 */
	public function addShop(Shop $shop);
	/**
	 * @param Shop $shop
	 */
	public function rmShop(Shop $shop);
	/**
	 * Closes the connection to the data provider, if necessary
	 * @return void
	 */
	public function close();
	/**
	 * @return bool
	 */
	public function isAvailable();
	/**
	 * @return int
	 */
	public function nextID();
}
