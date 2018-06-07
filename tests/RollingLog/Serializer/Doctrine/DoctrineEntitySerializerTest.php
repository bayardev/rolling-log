<?php

namespace Bayard\RollingLog\Tests\Serializer\Doctrine;

use Bayard\RollingLog\Serializer\DoctrineEntitySerializer;
use PHPUnit\Framework\TestCase;
use Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person;
use Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Family;
use Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

class DoctrineEntitySerializerTest extends TestCase
{

    protected $em;
    protected $serializer;
    protected $addressData;
    protected $personData;
    protected $familyData;

    protected $mockAddress;

    protected function setUp()
    {
        $this->serializer = new DoctrineEntitySerializer();
    }

    public function addressProvider()
    {
        return array(
            array("Aurice", "Route de campagne", 70),
            array("Saint Server", "Route de Aurice", 42),
            array("Cauna", "Route de Lourdes", 84),
            array("Montrouge", null, 62),
            array(true, 1024, "test"),
        );
    }

    public function personProvider()
    {
        return array(
            array("Remi", 22, 180, null,
                array("Aurice", "Route de campagne", 70)
            ),
            array("Damien", 19, 185, null,
                array("Saint Server", "Route de Aurice", 42)
            )
        );
    }

    public function familyProvider()
    {
        return array(
            array('Colet',
                array(
                    array("Remi", 22, 180, null,
                        array("Aurice", "Route de campagne", 70)
                    ),
                    array("Damien", 19, 185, null,
                        array("Saint Server", "Route de Aurice", 42)
                    )
                )
            )
        );
    }

    /**
     * @dataProvider addressProvider
     */
    public function testSimpleEntity($town, $street, $number)
    {
        $address = new Address();
        $address->setTown($town);
        $address->setStreet($street);
        $address->setNumber($number);

        $serialAdresse = $this->serializer->toArray($address);

        $this->assertTrue(is_array($serialAdresse));
        $this->assertTrue(count($serialAdresse) == 4);
        $this->assertEquals($town, $serialAdresse['town']);
        $this->assertEquals($street, $serialAdresse['street']);
        $this->assertEquals($number, $serialAdresse['number']);
        return $address;
    }

    /**
     * @dataProvider personProvider
     */
    public function testEntityWithOneToOne($firstName, $age, $size, $family, $tabAddress)
    {
        $address = new Address();
        $address->setTown($tabAddress[0]);
        $address->setStreet($tabAddress[1]);
        $address->setNumber($tabAddress[2]);

        $person = new Person();
        $person->setFirstName($firstName);
        $person->setAge($age);
        $person->setSize($size);
        $person->setAddress($address);

        for ($i = 1; $i < 3; $i++) {
            $serialPerson = $this->serializer->toArray($person, $i);

            $this->assertTrue(is_array($serialPerson));
            $this->assertTrue(count($serialPerson) == 5);
            $this->assertEquals($firstName, $serialPerson['firstName']);
            $this->assertEquals($age, $serialPerson['age']);
            $this->assertEquals($size, $serialPerson['size']);

            switch ($i) {
                case 1:
                    $this->assertEquals(null, $serialPerson['address']);
                    break;
                case 2:
                    $this->assertTrue(is_array($serialPerson));
                    $this->assertTrue(count($serialPerson) == 5);
                    $this->assertEquals($firstName, $serialPerson['firstName']);
                    $this->assertEquals($age, $serialPerson['age']);
                    $this->assertEquals($size, $serialPerson['size']);
                    $this->assertTrue(count($serialPerson['address']) == 4);
                    $this->assertEquals($tabAddress[0], $serialPerson['address']['town']);
                    $this->assertEquals($tabAddress[1], $serialPerson['address']['street']);
                    $this->assertEquals($tabAddress[2], $serialPerson['address']['number']);
                    break;
            }
        }
    }

    /**
     * @dataProvider familyProvider
     */
    public function testEntityWithManyToOne($name, $persons)
    {
        $family = new Family();
        $family->setName($name);

        $person1 = new Person();
        $person1->setFirstName($persons[0][0]);
        $person1->setAge($persons[0][1]);
        $person1->setSize($persons[0][2]);

        $address1 = new Address();
        $address1->setTown($persons[0][4][0]);
        $address1->setStreet($persons[0][4][1]);
        $address1->setNumber($persons[0][4][2]);
        $person1->setAddress($address1);

        $person2 = new Person();
        $person2->setFirstName($persons[1][0]);
        $person2->setAge($persons[1][1]);
        $person2->setSize($persons[1][2]);
        $address2 = new Address();
        $address2->setTown($persons[1][4][0]);
        $address2->setStreet($persons[1][4][1]);
        $address2->setNumber($persons[1][4][2]);
        $person2->setAddress($address2);

        $this->em = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $this->em->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'Family')));

        $arrayCollection = new ArrayCollection(array($person1, $person2));
        $this->assertContainsOnlyInstancesOf(ArrayCollection::class, [$arrayCollection]);

        $persistentCollection = new PersistentCollection(
            $this->em,
            $this->em->getClassMetadata('name'),
            $arrayCollection
        );
        $this->assertContainsOnlyInstancesOf(PersistentCollection::class, [$persistentCollection]);

        $family->setPersons($persistentCollection);


        for ($i = 1; $i < 4; $i++) {
            $serialFamily = $this->serializer->toArray($family, $i);

            $this->assertTrue(is_array($serialFamily));
            $this->assertTrue(count($serialFamily) == 3);
            $this->assertEquals($name, $serialFamily['name']);

            $this->assertTrue(is_array($serialFamily['persons']));

            switch ($i) {
                case 1:
                    $this->assertTrue(count($serialFamily['persons']) == 1);

                    $this->assertEquals(null, $serialFamily['persons']['id'][0]);
                    $this->assertEquals(null, $serialFamily['persons']['id'][1]);
                    break;
                case 2:
                    $this->assertTrue(count($serialFamily['persons']) == 2);

                    $this->assertTrue(is_array($serialFamily['persons'][0]));
                    $this->assertTrue(count($serialFamily['persons'][0]) == 5);
                    $this->assertEquals(null, $serialFamily['persons'][0]['id']);
                    $this->assertEquals($persons[0][0], $serialFamily['persons'][0]['firstName']);
                    $this->assertEquals($persons[0][1], $serialFamily['persons'][0]['age']);
                    $this->assertEquals($persons[0][2], $serialFamily['persons'][0]['size']);
                    $this->assertEquals(null, $serialFamily['persons'][0]['address']);


                    $this->assertTrue(is_array($serialFamily['persons'][1]));
                    $this->assertTrue(count($serialFamily['persons'][1]) == 5);
                    $this->assertEquals(null, $serialFamily['persons'][0]['id']);
                    $this->assertEquals($persons[1][0], $serialFamily['persons'][1]['firstName']);
                    $this->assertEquals($persons[1][1], $serialFamily['persons'][1]['age']);
                    $this->assertEquals($persons[1][2], $serialFamily['persons'][1]['size']);
                    $this->assertEquals(null, $serialFamily['persons'][0]['address']);
                    break;
                case 3:
                    $this->assertTrue(count($serialFamily['persons']) == 2);

                    $this->assertTrue(is_array($serialFamily['persons'][0]));
                    $this->assertTrue(count($serialFamily['persons'][0]) == 5);
                    $this->assertEquals(null, $serialFamily['persons'][0]['id']);
                    $this->assertEquals($persons[0][0], $serialFamily['persons'][0]['firstName']);
                    $this->assertEquals($persons[0][1], $serialFamily['persons'][0]['age']);
                    $this->assertEquals($persons[0][2], $serialFamily['persons'][0]['size']);
                    $this->assertTrue(is_array($serialFamily['persons'][0]['address']));
                    $this->assertTrue(count($serialFamily['persons'][0]['address']) == 4);
                    $this->assertEquals(null, $serialFamily['persons'][0]['address']['id']);
                    $this->assertEquals($persons[0][4][0], $serialFamily['persons'][0]['address']['town']);
                    $this->assertEquals($persons[0][4][1], $serialFamily['persons'][0]['address']['street']);
                    $this->assertEquals($persons[0][4][2], $serialFamily['persons'][0]['address']['number']);


                    $this->assertTrue(is_array($serialFamily['persons'][1]));
                    $this->assertTrue(count($serialFamily['persons'][1]) == 5);
                    $this->assertEquals(null, $serialFamily['persons'][0]['id']);
                    $this->assertEquals($persons[1][0], $serialFamily['persons'][1]['firstName']);
                    $this->assertEquals($persons[1][1], $serialFamily['persons'][1]['age']);
                    $this->assertEquals($persons[1][2], $serialFamily['persons'][1]['size']);
                    $this->assertTrue(is_array($serialFamily['persons'][1]['address']));
                    $this->assertTrue(count($serialFamily['persons'][1]['address']) == 4);
                    $this->assertEquals(null, $serialFamily['persons'][1]['address']['id']);
                    $this->assertEquals($persons[1][4][0], $serialFamily['persons'][1]['address']['town']);
                    $this->assertEquals($persons[1][4][1], $serialFamily['persons'][1]['address']['street']);
                    $this->assertEquals($persons[1][4][2], $serialFamily['persons'][1]['address']['number']);
                    break;
            }
        }
    }
}
