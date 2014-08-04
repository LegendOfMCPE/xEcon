<?php

namespace xecon\tax\tax;

interface Tax{
	public function getName();
	public function init(array $args);
	public function getPlugin();
}
