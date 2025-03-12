<?php

namespace Test\Container\Fixtures;

class C {

	public B $b;

	public function __construct( B $b ) {
		$this->b = $b;
	}
}
