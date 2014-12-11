<?php

namespace xecon\tax;

interface ExemptionCommand{
	public function getName();
	public function getValue($exp);
}
