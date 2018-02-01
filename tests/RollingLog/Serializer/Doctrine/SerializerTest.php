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

	protected $addressData = array(
		array("Aurice", "Route de campagne", 70), 
		array("Saint Server", "Route de Aurice", 42), 
		array("Cauna", "Route de Lourdes", 84),
	);

	protected $personData = array(
		array("Remi", 22, 180, null), 
		array("Damien", 19, 185, null)
	);

	protected $familyData =  array(
		array('Colet', null)
	);

	/**
	 * testSimpleEntity => Ce premier test premier de voir si la sérialization ce passe correctement avec une entité doctrine simple.
	 */
	public function testSimpleEntity()
	{
		//Importation de la partie Doctrine pour recevoir la variable $entityManager
		require("bootstrap.php");

		//Création de notre Entité simple (ici pour l'exemple nous créons une adresse)
		$address = new Address();
		$addressMethod = array();

		$i = 0;
		foreach(get_class_methods($address) as $method) {
			if(strncmp($method, "set", 3) == 0){
				$addressMethod[] = substr($method, 3);
				$address->$method($this->addressData[0][$i++]);
			}
		}

		//Nous hydraton l'entité en l'enregistront en base de donnée
		$entityManager->persist($address);
		$entityManager->flush();

		//Nous récupérons l'objet enregistré en base de données
		//Cela pourrais être plus simple de sérializer directement
		//l'entité créé au début, mais grâce à cela nous nous mettons
		//dans le même environnement d'une utilisation normal du
		//sérializer de RollingLog
		$addressRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$addressRequest = $addressRepository->findOneBy([lcfirst($addressMethod[0]) => $this->addressData[0][0]]);
		$serializer = $this->toArray($addressRequest);

		//Nous faisons enfin les vérification. Pour ce faire nous
		//avons conditionné l'attribut "town" de l'entité "Address"
		//en indiquant qu'il sera unique. l'ID étant générer durant
		//l'enregistrement, cela nous permet de bien choisir la 
		//bonne entitée. Bien sur, cela ne reflette pas du tout
		//la vrai magnière dont serais conditionné la ville d'une
		//adresse, mais cela est fais juste pour l'exemple et
		//nous permet de faire correctement les tests.
		$i = 0;
		foreach ($serializer as $key => $value) {

			if(strcmp($key, "id") == 0) {
				//Verification qu'il il y a bien l'ID
				$this->assertEquals($key, "id");

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $addressRequest->getId());
			} else {
				//Verification des clé dans le sérializer
				$this->assertEquals($key, lcfirst($addressMethod[$i]));

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $address->{"get".$addressMethod[$i++]}());
			}
		}

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

		//Création de notre Entité simple (ici pour l'exemple nous créons une adresse)
		$address = new Address();
		$person = new Person();

		$personMethod = array();

		$i = 0;
		foreach(get_class_methods($address) as $method)
			if(strncmp($method, "set", 3) == 0)
				$address->$method($this->addressData[1][$i++]);

		$i = 0;
		foreach(get_class_methods($person) as $method) {
			if(strncmp($method, "set", 3) == 0){
				$personMethod[] = substr($method, 3);
				$person->$method($this->personData[0][$i++]);
			}
		}

		$person->setAddress($address);

		//Nous hydraton l'entité en l'enregistront en base de donnée
		$entityManager->persist($address);
		$entityManager->persist($person);
		$entityManager->flush();

		$personRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$personRequest = $personRepository->findOneBy([lcfirst($personMethod[0]) => $this->personData[0][0]]);
		$serializerPerson = $this->toArray($personRequest);

		$i = 0;
		foreach ($serializerPerson as $key => $value) {

			if(strcmp($key, "id") == 0) {
				//Verification qu'il il y a bien l'ID
				$this->assertEquals($key, "id");

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $personRequest->getId());
			} else if(strcmp($key, "address") == 0){
				//Verification des clé dans le sérializer
				$this->assertEquals($key, lcfirst($personMethod[$i]));

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $person->{"get".$personMethod[$i++]}()->getId());
			} else {
				//Verification des clé dans le sérializer
				$this->assertEquals($key, lcfirst($personMethod[$i]));

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $person->{"get".$personMethod[$i++]}());
			}
		}

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

		//Création de notre Entité simple (ici pour l'exemple nous créons une adresse)
		$address = new Address();
		$person = new Person();

		$personMethod = array();

		$i = 0;
		foreach(get_class_methods($address) as $method) {
			if(strncmp($method, "set", 3) == 0){
				$addressMethod[] = substr($method, 3);
				$address->$method($this->addressData[2][$i++]);
			}
		}

		$i = 0;
		foreach(get_class_methods($person) as $method) {
			if(strncmp($method, "set", 3) == 0){
				$personMethod[] = substr($method, 3);
				$person->$method($this->personData[1][$i++]);
			}
		}

		$person->setAddress($address);

		//Nous hydraton l'entité en l'enregistront en base de donnée
		$entityManager->persist($address);
		$entityManager->persist($person);
		$entityManager->flush();

		$personRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$personRequest = $personRepository->findOneBy([lcfirst($personMethod[0]) => $this->personData[1][0]]);
		$serializerPerson = $this->toArray($personRequest, 2);

		$i = 0;
		foreach ($serializerPerson as $key => $value) {

			if(strcmp($key, "id") == 0) {
				//Verification qu'il il y a bien l'ID
				$this->assertEquals($key, "id");

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $personRequest->getId());
			} else if(strcmp($key, "address") == 0){
				//Verification des clé dans le sérializer
				$this->assertEquals($key, lcfirst($personMethod[$i]));

				$j = 0;
				foreach ($value as $key2 => $value2) {

					if(strcmp($key2, "id") == 0) {
						//Verification qu'il il y a bien l'ID
						$this->assertEquals($key2, "id");

						//Verification de la donnée dans le sérializer à clé $key2
						$this->assertEquals($value2, $personRequest->getAddress()->getId());
					} else {
						//Verification des clé dans le sérializer
						$this->assertEquals($key2, lcfirst($addressMethod[$j]));

						//Verification de la donnée dans le sérializer à clé $key2
						$this->assertEquals($value2, $address->{"get".$addressMethod[$j++]}());
					}
				}
			} else {
				//Verification des clé dans le sérializer
				$this->assertEquals($key, lcfirst($personMethod[$i]));

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $person->{"get".$personMethod[$i++]}());
			}
		}

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

		//Création de notre Entité simple (ici pour l'exemple nous créons une adresse)
		$address1 = new Address();
		$person1 = new Person();
		$address2 = new Address();
		$person2 = new Person();
		$family = new Family();


		$person1Method = array();
		$person2Method = array();
		$familyMethod = array();

		for($j = 1; $j < 3; $j++){
			$i = 0;
			foreach(get_class_methods(${'address'.$j}) as $method)
				if(strncmp($method, "set", 3) == 0)
					${'address'.$j}->$method($this->addressData[$j][$i++]);

			$i = 0;
			foreach(get_class_methods(${'person'.$j}) as $method)
				if(strncmp($method, "set", 3) == 0 || strncmp($method, "add", 3) == 0) {
					${'person'.$j.'Method'}[] = substr($method, 3);
					${'person'.$j}->$method($this->personData[$j-1][$i++]);
				}
		}

		$i = 0;
		foreach(get_class_methods($family) as $method) {
			if(strncmp($method, "set", 3) == 0){
				$familyMethod[] = substr($method, 3);
				$family->$method($this->familyData[0][$i++]);
			}
		}

		$person1->setAddress($address1);
		$person2->setAddress($address2);
		$family->addPerson($person1);
		$family->addPerson($person2);

		$entityManager->persist($address1);
		$entityManager->persist($address2);
		$entityManager->persist($family);
		$entityManager->flush();

		$person1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person1Request = $person1Repository->findOneBy([lcfirst($person1Method[0]) => $this->personData[0][0]]);
		$serializerPerson1 = $this->toArray($person1Request);

		$person2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person2Request = $person2Repository->findOneBy([lcfirst($person2Method[0]) => $this->personData[1][0]]);
		$serializerPerson2 = $this->toArray($person2Request);

		$familyRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Family');
		$familyRequest = $familyRepository->findOneBy([lcfirst($familyMethod[0]) => $this->familyData[0]]);
		$serializerFamily = $this->toArray($familyRequest);

		$i = 0;
		foreach ($serializerFamily as $key => $value) {

			if(strcmp($key, "id") == 0) {
				//Verification qu'il il y a bien l'ID
				$this->assertEquals($key, "id");

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $familyRequest->getId());
			} else if(strcmp($key, "persons") == 0){
				//Verification des clé dans le sérializer
				$this->assertEquals($key, "persons");

				foreach ($value as $key2 => $value2) {

					$this->assertEquals($key2, "id");
					$j = 1;
					foreach ($value2 as $value3)
						$this->assertEquals($value3, ${'person'.$j++.'Request'}->getId());
				}
			} else {
				//Verification des clé dans le sérializer
				$this->assertEquals($key, lcfirst($familyMethod[$i]));

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $family->{"get".$familyMethod[$i++]}());
			}
		}

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

		//Création de notre Entité simple (ici pour l'exemple nous créons une adresse)
		$address1 = new Address();
		$person1 = new Person();
		$address2 = new Address();
		$person2 = new Person();
		$family = new Family();

		$address1Method = array();
		$person1Method = array();
		$address2Method = array();
		$person2Method = array();
		$familyMethod = array();

		for($j = 1; $j < 3; $j++){
			$i = 0;
			foreach(get_class_methods(${'address'.$j}) as $method) {
				if(strncmp($method, "set", 3) == 0){
					${'address'.$j.'Method'}[] = substr($method, 3);
					${'address'.$j}->$method($this->addressData[$j][$i++]);
				}
			}

			$i = 0;
			foreach(get_class_methods(${'person'.$j}) as $method) {
				if(strncmp($method, "set", 3) == 0 || strncmp($method, "add", 3) == 0){
					${'person'.$j.'Method'}[] = substr($method, 3);
					${'person'.$j}->$method($this->personData[$j-1][$i++]);
				}
			}
		}

		$i = 0;
		foreach(get_class_methods($family) as $method) {
			if(strncmp($method, "set", 3) == 0){
				$familyMethod[] = substr($method, 3);
				$family->$method($this->familyData[0][$i++]);
			}
		}

		$person1->setAddress($address1);
		$person2->setAddress($address2);
		$family->addPerson($person1);
		$family->addPerson($person2);

		$entityManager->persist($address1);
		$entityManager->persist($address2);
		$entityManager->persist($family);
		$entityManager->flush();


		$address1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address1Request = $address1Repository->findOneBy([lcfirst($address1Method[0]) => $this->addressData[1][0]]);
		$serializerAddress1 = $this->toArray($address1Request);

		$person1Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person1Request = $person1Repository->findOneBy([lcfirst($person1Method[0]) => $this->personData[0][0]]);
		$serializerPerson1 = $this->toArray($person1Request);

		$address2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Address');
		$address2Request = $address2Repository->findOneBy([lcfirst($address2Method[0]) => $this->addressData[2][0]]);
		$serializerAddress2 = $this->toArray($address2Request);

		$person2Repository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Person');
		$person2Request = $person2Repository->findOneBy([lcfirst($person2Method[0]) => $this->personData[1][0]]);
		$serializerPerson2 = $this->toArray($person2Request);

		$familyRepository = $entityManager->getRepository('Bayard\RollingLog\Tests\Serializer\Doctrine\Entities\Family');
		$familyRequest = $familyRepository->findOneBy([lcfirst($familyMethod[0]) => $this->familyData[0]]);
		$serializerFamily = $this->toArray($familyRequest, 3);

		$i = 0;
		foreach ($serializerFamily as $key => $value) {

			if(strcmp($key, "id") == 0) {
				//Verification qu'il il y a bien l'ID
				$this->assertEquals($key, "id");

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $familyRequest->getId());
			} else if(strcmp($key, "persons") == 0){
				//Verification des clé dans le sérializer
				$this->assertEquals($key, "persons");

				$j = 1;
				foreach ($value as $key2 => $value2) {
					$k = 0;
					foreach ($value2 as $key3 => $value3) {
						if(strcmp($key3, "id") == 0) {
							//Verification qu'il il y a bien l'ID
							$this->assertEquals($key3, "id");

							//Verification de la donnée dans le sérializer à clé $key3
							$this->assertEquals($value3, ${'person'.$j.'Request'}->getId());
						} else if(strcmp($key3, "address") == 0){
							//Verification des clé dans le sérializer
							$this->assertEquals($key3, lcfirst(${'person'.$j.'Method'}[$k++]));

							$l = 0;
							foreach ($value3 as $key4 => $value4) {

								if(strcmp($key4, "id") == 0) {
									//Verification qu'il il y a bien l'ID
									$this->assertEquals($key4, "id");

									//Verification de la donnée dans le sérializer à clé $key4
									$this->assertEquals($value4, ${'person'.$j.'Request'}->getAddress()->getId());
								} else {
									//Verification des clé dans le sérializer
									$this->assertEquals($key4, lcfirst(${'address'.$j.'Method'}[$l]));

									//Verification de la donnée dans le sérializer à clé $key4
									$this->assertEquals($value4, ${'address'.$j.'Request'}->{"get".${'address'.$j.'Method'}[$l++]}());
								}
							}
						} else {
							//Verification des clé dans le sérializer
							$this->assertEquals($key3, lcfirst(${'person'.$j.'Method'}[$k]));

							//Verification de la donnée dans le sérializer à clé $key2
							$this->assertEquals($value3, ${'person'.$j}->{"get".${'person'.$j.'Method'}[$k++]}());
						}
					}
					$j++;
				}
			} else {
				//Verification des clé dans le sérializer
				$this->assertEquals($key, lcfirst($familyMethod[$i]));

				//Verification de la donnée dans le sérializer à clé $key
				$this->assertEquals($value, $family->{"get".$familyMethod[$i++]}());
			}
		}

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