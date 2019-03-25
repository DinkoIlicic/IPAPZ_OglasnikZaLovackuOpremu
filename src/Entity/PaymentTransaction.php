<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/25/19
 * Time: 1:10 PM
 */

namespace App\Entity;

/**
 * Class Transaction
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\PaymentTransactionRepository")
 * @\Doctrine\ORM\Mapping\HasLifecycleCallbacks()
 * @package                                                     App\Entity
 */
class PaymentTransaction
{
    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\User")
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Sold")
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $soldProduct;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     */
    private $method;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    private $transactionId;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="datetime")
     */
    private $chosenAt;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="datetime", nullable=true)
     */
    private $paidAt;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="boolean")
     */
    private $confirmed;

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param mixed $transactionId
     */
    public function setTransactionId($transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return mixed
     */
    public function getPaidAt()
    {
        return $this->paidAt;
    }

    /**
     * @param mixed $paidAt
     */
    public function setPaidAt($paidAt): void
    {
        $this->paidAt = $paidAt;
    }

    /**
     * @\Doctrine\ORM\Mapping\PrePersist()
     */
    public function onPrePersistPaidAt()
    {
        $this->paidAt = new \DateTime('now');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getChosenAt()
    {
        return $this->chosenAt;
    }

    /**
     * @param mixed $chosenAt
     */
    public function setChosenAt($chosenAt): void
    {
        $this->chosenAt = $chosenAt;
    }

    /**
     * @\Doctrine\ORM\Mapping\PrePersist()
     */
    public function onPrePersistChosenAt()
    {
        $this->chosenAt = new \DateTime('now');
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}
