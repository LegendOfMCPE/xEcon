<?php

namespace xecon\account;

interface Transactable{
	public function canPay($amount);
	public function canReceive($amount);
	public function pay(Transactable $other, $amount, $detail = "None", $force = false);
	public function add($amount);
	public function take($amount);
}
