<?php

namespace SS6\ShopBundle\Tests\Unit\Component\Router;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Component\Transformers\RemoveDuplicatesFromArrayTransformer;

class RemoveDuplicatesFromArrayTransformerTest extends PHPUnit_Framework_TestCase {

	public function testReverseTransform() {
		$array = ['a', 'b', 'a'];

		$transformer = new RemoveDuplicatesFromArrayTransformer();
		$this->assertSame(['a', 'b'], $transformer->reverseTransform($array));
	}

	public function testReverseTransformPresevesKeys() {
		$array = [0 => 'a', 10 => 'b', 20 => 'a'];

		$transformer = new RemoveDuplicatesFromArrayTransformer();
		$this->assertSame([0 => 'a', 10 => 'b'], $transformer->reverseTransform($array));
	}

	public function testReverseTransformComparesStrictly() {
		$array = ['0', 0, null, false];

		$transformer = new RemoveDuplicatesFromArrayTransformer();
		$this->assertSame(['0', 0, null, false], $transformer->reverseTransform($array));
	}

}