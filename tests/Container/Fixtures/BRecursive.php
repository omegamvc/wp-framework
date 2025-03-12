<?php

namespace Test\Container\Fixtures;

class BRecursive {

	public ARecursive $a;

	public function __construct( ARecursive $a ) {
		$this->a = $a;
	}
}
