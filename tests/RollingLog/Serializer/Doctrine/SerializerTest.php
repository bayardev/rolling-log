<?php

namespace Bayard\RollingLog\Tests\Serializer\Doctrine;

use Bayard\RollingLog\Serializer\DoctrineEntitySerializer;
use PHPUnit\Framework\TestCase;
use Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person;
use Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Family;
use Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;


/**
* 
*/
class SerializerTest extends TestCase
{
	use DoctrineEntitySerializer;


	/**
	 * testSimpleEntity => Ce premier test premier de voir si la sérialization ce passe correctement avec une entité doctrine simple.
	 */
	public function testSimpleEntity()
	{
		//Importation de la partie Doctrine pour recevoir la variable $entityManager
		require("bootstrap.php");

		//Création de notre Entité simple (ici pour l'exemple nous créons une adresse)
		$address = new Address();
		$address->setTown("Aurice");
		$address->setStreet("Route de campagne");
		$address->setNumber(70);

		//Nous hydraton l'entité en l'enregistront en base de donnée
		$entityManager->persist($address);
		$entityManager->flush();

		//Nous récupérons l'objet enregistré en base de données
		//Cela pourrais être plus simple de sérializer directement
		//l'entité créé au début, mais grâce à cela nous nous mettons
		//dans le même environnement d'une utilisation normal du
		//sérializer de RollingLog
		$addressRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$addressRequest = $addressRepository->findOneBy(["town" => "Aurice"]);
		$serializer = $this->toArray($addressRequest);

		//Nous faisons enfin les vérification. Pour ce faire nous
		//avons conditionné l'attribut "town" de l'entité "Address"
		//en indiquant qu'il sera unique. l'ID étant générer durant
		//l'enregistrement, cela nous permet de bien choisir la 
		//bonne entitée. Bien sur, cela ne reflette pas du tout
		//la vrai magnière dont serais conditionné la ville d'une
		//adresse, mais cela est fais juste pour l'exemple et
		//nous permet de faire correctement les tests.
		$this->assertEquals($serializer['town'], $address->getTown());
		$this->assertEquals($serializer['street'], $address->getStreet());
		$this->assertEquals($serializer['number'], $address->getNumber());

		//Nous vidons ensuite la table "Address" contenu dans la 
		//base de données pour le bon focntionnement des autres
		//tests
		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}
	}

	public function testEntityWithOneToOne()
	{
		require("bootstrap.php");

		$address = new Address();
		$address->setTown("Saint Server");
		$address->setStreet("Route de Aurice");
		$address->setNumber(42);

		$person = new Person();
		$person->setFirstName("Remi");
		$person->setAge(22);
		$person->setSize(180);
		$person->setAddress($address);

		$entityManager->persist($address);
		$entityManager->persist($person);
		$entityManager->flush();


		$addressRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$addressRequest = $addressRepository->findOneBy(["town" => "Saint Server"]);
		$serializerAddress = $this->toArray($addressRequest);

		$personRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$personRequest = $personRepository->findOneBy(["firstName" => "Remi"]);
		$serializerPerson = $this->toArray($personRequest);

		$this->assertEquals($serializerAddress['town'], $address->getTown());
		$this->assertEquals($serializerAddress['street'], $address->getStreet());
		$this->assertEquals($serializerAddress['number'], $address->getNumber());

		$this->assertEquals($serializerPerson['firstName'], $person->getFirstName());
		$this->assertEquals($serializerPerson['age'], $person->getAge());
		$this->assertEquals($serializerPerson['size'], $person->getSize());

		$addressInPersonRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$addressInPersonRequest = $addressInPersonRepository->findOneBy(["id" => $serializerPerson['address']]);
		$this->assertEquals($addressInPersonRequest->getTown(), $address->getTown());

		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}

		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}
	}


	public function testEntityWithOneToOneWithChangeDepth()
	{
		require("bootstrap.php");

		$address = new Address();
		$address->setTown("Cauna");
		$address->setStreet("Route de Lourdes");
		$address->setNumber(84);

		$person = new Person();
		$person->setFirstName("Damien");
		$person->setAge(19);
		$person->setSize(185);
		$person->setAddress($address);

		$entityManager->persist($address);
		$entityManager->persist($person);
		$entityManager->flush();


		$addressRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$addressRequest = $addressRepository->findOneBy(["town" => "Cauna"]);
		$serializerAddress = $this->toArray($addressRequest);

		$personRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$personRequest = $personRepository->findOneBy(["firstName" => "Damien"]);
		$serializerPerson = $this->toArray($personRequest, 2);

		$this->assertEquals($serializerAddress['town'], $address->getTown());
		$this->assertEquals($serializerAddress['street'], $address->getStreet());
		$this->assertEquals($serializerAddress['number'], $address->getNumber());

		$this->assertEquals($serializerPerson['firstName'], $person->getFirstName());
		$this->assertEquals($serializerPerson['age'], $person->getAge());
		$this->assertEquals($serializerPerson['size'], $person->getSize());
		$this->assertEquals($serializerPerson['address']['town'], $address->getTown());
		$this->assertEquals($serializerPerson['address']['street'], $address->getStreet());
		$this->assertEquals($serializerPerson['address']['number'], $address->getNumber());

		$addressInPersonRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$addressInPersonRequest = $addressInPersonRepository->findOneBy(["id" => $serializerPerson['address']['id']]);
		$this->assertEquals($addressInPersonRequest->getTown(), $address->getTown());

		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}

		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}
	}

	public function testEntityWithManyToOne()
	{
		require("bootstrap.php");

		$address1 = new Address();
		$address1->setTown("Saint Server");
		$address1->setStreet("Route de Aurice");
		$address1->setNumber(42);

		$person1 = new Person();
		$person1->setFirstName("Remi");
		$person1->setAge(22);
		$person1->setSize(180);
		$person1->setAddress($address1);

		$address2 = new Address();
		$address2->setTown("Cauna");
		$address2->setStreet("Route de Lourdes");
		$address2->setNumber(84);

		$person2 = new Person();
		$person2->setFirstName("Damien");
		$person2->setAge(19);
		$person2->setSize(185);
		$person2->setAddress($address2);

		$family = new Family();
		$family->setName("Colet");
		$family->addPerson($person1);
		$family->addPerson($person2);

		$entityManager->persist($address1);
		$entityManager->persist($address2);
		$entityManager->persist($family);
		$entityManager->flush();


		$address1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address1Request = $address1Repository->findOneBy(["town" => "Saint Server"]);
		$serializerAddress1 = $this->toArray($address1Request);

		$person1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person1Request = $person1Repository->findOneBy(["firstName" => "Remi"]);
		$serializerPerson1 = $this->toArray($person1Request);

		$this->assertEquals($serializerAddress1['town'], $address1->getTown());
		$this->assertEquals($serializerAddress1['street'], $address1->getStreet());
		$this->assertEquals($serializerAddress1['number'], $address1->getNumber());

		$this->assertEquals($serializerPerson1['firstName'], $person1->getFirstName());
		$this->assertEquals($serializerPerson1['age'], $person1->getAge());
		$this->assertEquals($serializerPerson1['size'], $person1->getSize());

		$address1InPerson1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address1InPerson1Request = $address1InPerson1Repository->findOneBy(["id" => $serializerPerson1['address']]);
		$this->assertEquals($address1InPerson1Request->getTown(), $address1->getTown());


		$address2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address2Request = $address2Repository->findOneBy(["town" => "Cauna"]);
		$serializerAddress2 = $this->toArray($address2Request);

		$person2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person2Request = $person2Repository->findOneBy(["firstName" => "Damien"]);
		$serializerPerson2 = $this->toArray($person2Request);

		$this->assertEquals($serializerAddress2['town'], $address2->getTown());
		$this->assertEquals($serializerAddress2['street'], $address2->getStreet());
		$this->assertEquals($serializerAddress2['number'], $address2->getNumber());

		$this->assertEquals($serializerPerson2['firstName'], $person2->getFirstName());
		$this->assertEquals($serializerPerson2['age'], $person2->getAge());
		$this->assertEquals($serializerPerson2['size'], $person2->getSize());

		$address2InPerson2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address2InPerson2Request = $address2InPerson2Repository->findOneBy(["id" => $serializerPerson2['address']]);
		$this->assertEquals($address2InPerson2Request->getTown(), $address2->getTown());

		$familyRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Family');
		$familyRequest = $familyRepository->findOneBy(["name" => "Colet"]);
		$serializerFamily = $this->toArray($familyRequest);
		$this->assertEquals($serializerFamily['name'], $family->getName());

		$person1InFamilyRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person1InFamilyRequest = $person1InFamilyRepository->findOneBy(["id" => $serializerFamily['persons']['id'][0]]);
		$this->assertEquals($person1InFamilyRequest->getFirstName(), $person1->getFirstName());

		$person2InFamilyRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person2InFamilyRequest = $person2InFamilyRepository->findOneBy(["id" => $serializerFamily['persons']['id'][1]]);
		$this->assertEquals($person2InFamilyRequest->getFirstName(), $person2->getFirstName());


		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}

		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}

		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Family');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}
	}

	public function testEntityWithManyToOneWithChangeDepth()
	{
		require("bootstrap.php");

		$address1 = new Address();
		$address1->setTown("Saint Server");
		$address1->setStreet("Route de Aurice");
		$address1->setNumber(42);

		$person1 = new Person();
		$person1->setFirstName("Remi");
		$person1->setAge(22);
		$person1->setSize(180);
		$person1->setAddress($address1);

		$address2 = new Address();
		$address2->setTown("Cauna");
		$address2->setStreet("Route de Lourdes");
		$address2->setNumber(84);

		$person2 = new Person();
		$person2->setFirstName("Damien");
		$person2->setAge(19);
		$person2->setSize(185);
		$person2->setAddress($address2);

		$family = new Family();
		$family->setName("Colet");
		$family->addPerson($person1);
		$family->addPerson($person2);

		$entityManager->persist($address1);
		$entityManager->persist($address2);
		$entityManager->persist($family);
		$entityManager->flush();


		$address1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address1Request = $address1Repository->findOneBy(["town" => "Saint Server"]);
		$serializerAddress1 = $this->toArray($address1Request, 3);

		$person1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person1Request = $person1Repository->findOneBy(["firstName" => "Remi"]);
		$serializerPerson1 = $this->toArray($person1Request, 3);

		$this->assertEquals($serializerAddress1['town'], $address1->getTown());
		$this->assertEquals($serializerAddress1['street'], $address1->getStreet());
		$this->assertEquals($serializerAddress1['number'], $address1->getNumber());

		$this->assertEquals($serializerPerson1['firstName'], $person1->getFirstName());
		$this->assertEquals($serializerPerson1['age'], $person1->getAge());
		$this->assertEquals($serializerPerson1['size'], $person1->getSize());

		$address1InPerson1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address1InPerson1Request = $address1InPerson1Repository->findOneBy(["id" => $serializerPerson1['address']]);
		$this->assertEquals($address1InPerson1Request->getTown(), $address1->getTown());


		$address2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address2Request = $address2Repository->findOneBy(["town" => "Cauna"]);
		$serializerAddress2 = $this->toArray($address2Request, 3);

		$person2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person2Request = $person2Repository->findOneBy(["firstName" => "Damien"]);
		$serializerPerson2 = $this->toArray($person2Request, 3);

		$this->assertEquals($serializerAddress2['town'], $address2->getTown());
		$this->assertEquals($serializerAddress2['street'], $address2->getStreet());
		$this->assertEquals($serializerAddress2['number'], $address2->getNumber());

		$this->assertEquals($serializerPerson2['firstName'], $person2->getFirstName());
		$this->assertEquals($serializerPerson2['age'], $person2->getAge());
		$this->assertEquals($serializerPerson2['size'], $person2->getSize());

		$address2InPerson2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address2InPerson2Request = $address2InPerson2Repository->findOneBy(["id" => $serializerPerson2['address']]);
		$this->assertEquals($address2InPerson2Request->getTown(), $address2->getTown());

		$familyRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Family');
		$familyRequest = $familyRepository->findOneBy(["name" => "Colet"]);
		$serializerFamily = $this->toArray($familyRequest, 3);
		$this->assertEquals($serializerFamily['name'], $family->getName());

		$this->assertEquals($serializerFamily['persons'][0]['firstName'], $person1->getFirstName());
		$this->assertEquals($serializerFamily['persons'][0]['age'], $person1->getAge());
		$this->assertEquals($serializerFamily['persons'][0]['size'], $person1->getSize());
		$this->assertEquals($serializerFamily['persons'][0]['address']['town'], $address1->getTown());
		$this->assertEquals($serializerFamily['persons'][0]['address']['street'], $address1->getStreet());
		$this->assertEquals($serializerFamily['persons'][0]['address']['number'], $address1->getNumber());

		$this->assertEquals($serializerFamily['persons'][1]['firstName'], $person2->getFirstName());
		$this->assertEquals($serializerFamily['persons'][1]['age'], $person2->getAge());
		$this->assertEquals($serializerFamily['persons'][1]['size'], $person2->getSize());
		$this->assertEquals($serializerFamily['persons'][1]['address']['town'], $address2->getTown());
		$this->assertEquals($serializerFamily['persons'][1]['address']['street'], $address2->getStreet());
		$this->assertEquals($serializerFamily['persons'][1]['address']['number'], $address2->getNumber());


		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}

		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}

		$cmd = $entityManager->getClassMetadata('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Family');
		$connection = $entityManager->getConnection();
		$connection->beginTransaction();

		try {
		    $connection->query('SET FOREIGN_KEY_CHECKS=0');
		    $connection->query('DELETE FROM '.$cmd->getTableName());

		    $connection->query('SET FOREIGN_KEY_CHECKS=1');
		    $connection->commit();
		} catch (\Exception $e) {
		    $connection->rollback();
		}
	}
}