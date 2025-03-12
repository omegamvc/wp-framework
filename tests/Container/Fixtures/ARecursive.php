<?php

namespace Test\Container\Fixtures;

class ARecursive {

	public BRecursive $b;

	public function __construct( BRecursive $b ) {
		$this->b = $b;
	}
}
