<?php

namespace xecon\account;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
//use pocketmine\item\Item;
use pocketmine\network\protocol;
use xecon\entity\Entity;
use xecon\entity\Service;

class Account implements InventoryHolder{
	/** @var string */
	protected $name;
	/** @var float */
	protected $amount;
	/** @var Entity */
	protected $entity;
	/** @var DummyInventory */
	protected $inventory;
	protected $maxContainable = 1000;
	protected $minAmount = 0;
//	/** @var int[] */
//	private $inventoryMoneySlots = [];
	private $containerTypes = [];
	private $liability = false;
//	public static function constructFromArray($name, Entity $entity, $data){
//		$constructor = $data["class"]."::constructInstance";
//		return $constructor($name, $entity, $data);
//	}
//	public static function constructInstance($name, Entity $entity, $data){
//		$inst = new Account($name, $data["amount"], $entity);
//		$inst->setMinAmount($data["min-amount"]);
//		$inst->setMaxContainable($data["max-containable"]);
//		$inst->setIsLiability($data["is-liability"]);
//		return $inst;
//	}
	/**
	 * @param string $name
	 * @param float $amount
	 * @param Entity $entity
	 * @param Inventory|null $inventory
	 * @param string[] $containerTypes
	 */
	public function __construct($name, $amount, Entity $entity, Inventory $inventory = null, array $containerTypes = []){
		$this->name = $name;
		$this->amount = $amount;
		$this->entity = $entity;
		$this->inventory = (!($inventory instanceof Inventory)) ? new DummyInventory($this):$inventory;
		foreach($containerTypes as $type){
			$maxContainable = constant($type."::PER_AMOUNT") * constant($type."::MAX_STACK");
			$this->containerTypes[$maxContainable] = $type;
		}
		krsort($this->containerTypes, SORT_NUMERIC);
	}
	public function getMaxContainable(){
		return $this->maxContainable;
	}
	public function setMaxContainable($cnt){
		$this->maxContainable = $cnt;
	}
	public function setMinAmount($a = 0){
		$this->minAmount = $a;
	}
	public function getName(){
		return $this->name;
	}
	public function setIsLiability($bool){
		$this->liability = $bool;
	}
	public function isLiability(){
		return $this->liability;
	}
	public function getAmount(){
		return $this->amount;
	}
	/**
	 * This raw function is only for internal use. Do NOT call this method. Call Account::pay() instead.
	 * @param $amount
	 * @return bool
	 */
	public function add($amount){
		return $this->setAmount($this->getAmount() + $amount);
	}
	/**
	 * This raw function is only for internal use. Calling this method is discouraged unless logging of transactions is unwanted. Call Account::pay() instead.
	 * @param int $amount
	 * @return bool
	 */
	public function take($amount){
		return $this->setAmount($this->getAmount() - $amount);
	}
	/**
	 * This raw function is only for internal use. Calling this method is discouraged unless logging of transactions is unwanted. Call Account::pay() instead.
	 * @param int $amount
	 * @return bool
	 */
	public function setAmount($amount){
		if($amount > $this->maxContainable or $amount < $this->minAmount){
			if($this->entity instanceof Service){
				return true;
			}
			return false;
		}
		$this->amount = $amount;
//		$this->tidyInventory($amount);
		return true;
	}
	public function getInventory(){
		return $this->inventory;
	}
//	public function tidyInventory($new){
//		$this->clearInventoryMoney();
//		$this->addInventoryMoney($new);
//	}
//	protected function clearInventoryMoney(){
//		while(count($this->inventoryMoneySlots) > 0){
//			$this->getInventory()->setItem(array_shift($this->inventoryMoneySlots), Item::get(0));
//		}
//	}
//	protected function addInventoryMoney($amount){
//		$curAmt = $amount;
//		$items = [];
//		$availableSlotsLeft = $this->getInventory()->all(Item::get(0));
//		foreach($this->containerTypes as $type){
//			$maxStack = constant($type."::MAX_STACK");
//			$perAmount = constant($type."::PER_AMOUNT");
//			if($perAmount > $curAmt){
//				continue;
//			}
//			$count = 0;
//			while($curAmt >= $perAmount and $count < $maxStack * 16 and $availableSlotsLeft - ($count / 16) > 0){
//				$count++;
//				$curAmt -= $perAmount;
//			}
//			$items[$type] = $count;
//			if($availableSlotsLeft === 0 or $curAmt === 0){
//				break;
//			}
//		}
//		if($curAmt > 0){
//			$this->entity->sendMessage("Your \$$curAmt has been dropped due to your {$this->getName()} inventory is full.");
//		}
////		$slots = [];
////		foreach($items as $type => $count){
////			$id = constant($type."::ID");
////			$amount = (int) floor($count / 16);
////			$meta = $count % 16;
////			// TODO this complex maths got my head exploded.
////		}
//	}
	/**
	 * This is an API method. You are encouraged to use this method (with $account
	 * as \xecon\Main::getService()->getService($serviceName)) or transactWithAccountTo()
	 * instead of Account::add(), Account::take() or Account::setAmount(). Look at
	 * <a href="https://github.com/LegendOfMCPE/xEcon/wiki/developer's%20guide">the article about
	 * <i>double entry</i> on the wiki</a> for why using this method is encouraged.
	 * @param Account $other
	 * @param number $amount
	 * @param string $detail
	 * @param bool $force
	 * @return bool
	 */
	public function pay(Account $other, $amount, $detail = "None", $force = false){
		if($detail === ""){
			$detail = "None";
		}
		if(!$this->canPay($amount) and !$force){
			return false;
		}
		if($other->add($amount) and $this->take($amount)){ // why did I mess these two up...
			$this->getEntity()->getMain()->logTransaction($this, $other, $amount, $detail);
			return true;
		}
		return false;
	}
	/**
	 * This is an API method. Developers are encouraged to use either this method or pay().
	 * @param $amount
	 * @param Account $other
	 * @param null $detail
	 * @return int
	 */
	public function transactWithAccountTo($amount, Account $other, $detail = null){
		if($detail === null){
			$detail = "transact to \$$amount";
		}
		if($this->getAmount() === $amount){
			return 0;
		}
		if($this->getAmount() > $amount){
			return $this->pay($other, $this->getAmount() - $amount, $detail) ?
					($amount - $this->getAmount()):0;
		}
		else{
			return $other->pay($this, $amount - $this->getAmount(), $detail) ?
					($amount - $this->getAmount()):0;
		}
	}
	public function canPay($amount){
		return ($this->entity instanceof Service) or ($this->amount - $amount) >= $this->minAmount;
	}
	/**
	 * @return \xecon\entity\Entity
	 */
	public function getEntity(){
		return $this->entity;
	}
//	public function toArray(){
//		return [
//			"amount" => $this->getAmount(),
//			"max-containable" => $this->getMaxContainable(),
//			"min-amount" => $this->minAmount,
//			"class" => get_class($this),
//		];
//	}
	/**
	 * @return int
	 */
	public function getMinAmount(){
		return $this->minAmount;
	}
	public function getUniqueName(){
		return implode("/", [$this->entity->getAbsolutePrefix(), $this->entity->getName(), $this->getName()]);
	}
}
