<?php

namespace Test\Container\Fixtures;

class D {

	public string $message;
	public A $a;

	public function __construct( string $message, A $a ) {
		$this->message = $message;
		$this->a = $a;
	}
}
