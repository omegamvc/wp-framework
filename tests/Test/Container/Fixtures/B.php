<?php

namespace Test\Container\Fixtures;

class B {

	public A $a;

	public function __construct( A $a ) {
		$this->a = $a;
	}
}
