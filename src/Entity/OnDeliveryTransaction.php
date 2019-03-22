<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/22/19
 * Time: 12:47 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class OnDeliveryTransaction
 * @package App\Entity
 * @ORM\Entity(repositoryClass="App\Repository\OnDeliveryTransactionRepository")
 */
class OnDeliveryTransaction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sold")
     * @ORM\JoinColumn(nullable=false)
     */
    private $soldProduct;

    /**
     * @ORM\Column(type="datetime")
     */
    private $chosenAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $confirmed;

    /**
     * @return mixed
     */
    public function getSoldProduct()
    {
        return $this->soldProduct;
    }

    /**
     * @param mixed $soldProduct
     */
    public function setSoldProduct($soldProduct): void
    {
        $this->soldProduct = $soldProduct;
    }

    /**
     * @return mixed
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param mixed $confirmed
     */
    public function setConfirmed($confirmed): void
    {
        $this->confirmed = $confirmed;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $chosenAt
     */
    public function setChosenAt($chosenAt): void
    {
        $this->chosenAt = $chosenAt;
    }

    /**
     * @return mixed
     */
    public function getChosenAt()
    {
        return $this->chosenAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->chosenAt = new \DateTime('now');
    }
}
