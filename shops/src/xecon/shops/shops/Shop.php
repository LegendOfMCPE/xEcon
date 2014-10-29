<?php

namespace xecon\shops\shops;

use pocketmine\block\Air;
use pocketmine\inventory\Inventory;
use pocketmine\item\ItemBlock;
use pocketmine\item\Item;
use xecon\account\Account;
use xecon\shops\Shops;

trait Shop{
	private $id;
	/** @var Item */
	private $item;
	/** @var bool */
	private $sell;
	/** @var bool */
	private $checkDamage;
	/** @var double */
	private $amount;
	private $main;
	private $price;
	public function __construct(Shops $shops, Item $item, $sell, $amount, $price, $checkDamage = true, $id = false){
		$this->main = $shops;
		$this->item = $item;
		$this->sell = $sell;
		$this->amount = $amount;
		$this->price = $price;
		$this->checkDamage = $checkDamage;
		if($id === false){
			$id = $shops->getProvider()->nextID();
		}
		$this->id = $id;
	}
	/**
	 * @return Item
	 */
	public function getItem(){
		return $this->item;
	}
	/**
	 * @param Item $item
	 */
	public function setItem($item){
		$this->item = $item;
	}
	/**
	 * @return boolean
	 */
	public function isSell(){
		return $this->sell;
	}
	/**
	 * @return float
	 */
	public function getAmount(){
		return $this->amount;
	}
	/**
	 * @param float $amount
	 */
	public function setAmount($amount){
		$this->amount = $amount;
	}
	/**
	 * @return mixed
	 */
	public function getPrice(){
		return $this->price;
	}
	/**
	 * @param mixed $price
	 */
	public function setPrice($price){
		$this->price = $price;
	}
	/**
	 * @return boolean
	 */
	public function isCheckDamage(){
		return $this->checkDamage;
	}
	/**
	 * @param boolean $checkDamage
	 */
	public function setCheckDamage($checkDamage){
		$this->checkDamage = $checkDamage;
	}
	public function trade(Account $account, $amount = 1, Inventory $store){
		$required = $amount * $this->getAmount();
		if($this->isSell()){
			$cnt = 0;
			foreach($store->getContents() as $item){
				if($item->getID() === $this->getItem()->getID() and (!$this->isCheckDamage() or $item->getDamage() === $this->getItem()->getDamage())){
					$cnt += $item->getCount();
				}
			}
			if($cnt <= $required){
				return false;
			}
			foreach($store->getContents() as $slot => $item){
				if($item->getID() === $this->getItem()->getID() and (!$this->isCheckDamage() or $item->getDamage() === $this->getItem()->getDamage())){
					$count = $item->getCount();
					if($required >= $count){
						$store->setItem($slot, new ItemBlock(new Air));
						if($count === $required){
							break;
						}
						$required -= $count;
					}
					else{
						$item->setCount($count - $required);
						break;
					}
				}
			}
			$this->main->getService()->pay($account, $this->getPrice() * $amount, "Sold {$this->item->getName()} to an xEcon shop");
			return true;
		}
		else{
			if(!$account->canPay($this->getPrice() * $amount)){
				return false;
			}
			$item = clone $this->item;
			$item->setCount($amount * $this->getAmount());
			$store->addItem($item);
			$account->pay($this->main->getService(), $this->getPrice() * $amount, "Bought {$this->item->getName()} from an xEcon shop");
			return true;
		}
	}
	/**
	 * @return int
	 */
	public function getID(){
		return $this->id;
	}
	public function getMetadata(){
		$out = 0;
		if($this->isCheckDamage()){
			$out |= 0b01;
		}
		if($this->isSell()){
			$out |= 0b10;
		}
		return $out;
	}
}
