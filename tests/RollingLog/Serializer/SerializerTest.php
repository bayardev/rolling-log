<?php

namespace Bayard\RollingLog\Tests\Serializer;

use Bayard\RollingLog\Serializer\DoctrineEntitySerializer;
use PHPUnit\Framework\TestCase;
use Bayard\RollingLog\Tests\Serializer\Person;
use Bayard\RollingLog\Tests\Serializer\Family;

/**
* 
*/
class SerializerTest extends TestCase
{
	use DoctrineEntitySerializer;
	
	public function testFirst()
	{
		$person = new Person();
		$person->setFirstName("Rémi");
		$person->setAge(9);
		$person->setSize(180);
		$family = new Family();
		$family->setName("Colet");
		$family->addPerson($person);
		var_dump($this->toArray($family));
		$this->assertEquals(9, $person->getAge());
	}
}