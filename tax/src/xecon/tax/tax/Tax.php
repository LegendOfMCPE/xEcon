<?php

namespace xecon\tax\tax;

interface Tax{
	public function __construct(array $args, TaxWrapper $wrapper);
	public function getType();
	public function getSourcePlugin();
	public function execute($ent);
}
