<?php

namespace Bayard\RollingLog\Tests\Serializer\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adress
 * @Entity @Table(name="address")
 */
class Address{	
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
     * @Column(name="town", type="string", length=255, unique=true)
     */
	private $town;

    /**
     * @var string
     *
     * @Column(name="street", type="string", length=255)
     */
    private $street;

    /**
     * @var int
     *
     * @Column(name="number", type="integer")
     */
    private $number;

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
     * Set town.
     *
     * @param string $town
     *
     * @return Address
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town.
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set street.
     *
     * @param string $street
     *
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set number.
     *
     * @param int $number
     *
     * @return Address
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number.
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }
}