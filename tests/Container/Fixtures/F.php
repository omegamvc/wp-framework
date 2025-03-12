<?php

namespace Test\Container\Fixtures;

class F {

	public string $message;

	public function __construct( string $message ) {
		$this->message = $message;
	}
}
