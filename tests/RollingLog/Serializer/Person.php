<?php

namespace Bayard\RollingLog\Tests\Serializer;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table(name="person")
 */
class Person
{	
	/**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
	private $id;

	/**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     */
	private $firstName;

	/**
     * @var int
     *
     * @ORM\Column(name="age", type="integer")
     */
	private $age;

	/**
     * @var int
     *
     * @ORM\Column(name="size", type="integer")
     */
	private $size;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return Advert
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set age.
     *
     * @param int $age
     *
     * @return Advert
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age.
     *
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set size.
     *
     * @param int $size
     *
     * @return Advert
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}