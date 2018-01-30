<?php

namespace Bayard\RollingLog\Tests\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Family
 *
 * @ORM\Table(name="person")
 */
class Family
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
    * @ORM\OneToMany(targetEntity="Person")
    * @ORM\JoinColumn(nullable=true)
    */
    private $persons;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
    }

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
    public function addPerson(Person $person)
    {
        $this->persons[] = $person;

        return $this;
    }

    /**
     * Remove person.
     *
     * @param Person $person
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePerson(Person $person)
    {
        return $this->persons->removeElement($person);
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