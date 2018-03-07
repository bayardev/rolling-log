<?php

namespace Bayard\RollingLog\Tests\Serializer\Doctrine\Entities;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Family
 *
 * @Entity @Table(name="family")
 */
class Family
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
     * @Column(name="name", type="string", length=255)
     */
    private $name;

    /**
    * @OneToMany(targetEntity="Person", cascade={"persist", "remove"}, mappedBy="family")
    */
    private $persons;

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
     * Set name.
     *
     * @param string $name
     *
     * @return Advert
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add person.
     *
     * @param Person $person
     *
     * @return Family
     */
    public function setPersons(PersistentCollection $tabPersons)
    {
        $this->persons = $tabPersons;

        return $this;
    }

    /**
     * Get persons.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPersons()
    {
        return $this->persons;
    }
}