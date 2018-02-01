<?php

namespace Bayard\RollingLog\Tests\Serializer\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @Entity @Table(name="person")
 */
class Person
{	
	/**
     * @var int
     *
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
	private $id;

	/**
     * @var string
     *
     * @Column(name="firstName", type="string", length=255, unique=true)
     */
	private $firstName;

	/**
     * @var int
     *
     * @Column(name="age", type="integer")
     */
	private $age;

	/**
     * @var int
     *
     * @Column(name="size", type="integer")
     */
	private $size;

    /**
    * @ManyToOne(targetEntity="Family", inversedBy="persons")
    */
    private $family;

    /**
    * @OneToOne(targetEntity="Address")
    * @JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
    */
    private $address;

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

    /**
     * Set address.
     *
     * @param \BayardTest\PlatformBundle\Entity\Image $address
     *
     * @return Person
     */
    public function setAddress(Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return \BayardTest\PlatformBundle\Entity\Image
     */
    public function getAddress()
    {
        return $this->address;
    }
}