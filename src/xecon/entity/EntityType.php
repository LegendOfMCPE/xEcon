<?php

namespace xecon\entity;

interface EntityType{
	public function getName();
	public function getAbsolutePrefix();
	public function getClass();
}
