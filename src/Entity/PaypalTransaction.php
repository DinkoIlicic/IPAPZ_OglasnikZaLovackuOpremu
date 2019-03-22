<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/22/19
 * Time: 9:28 AM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class PaypalTransaction
 *
 * @ORM\Entity(repositoryClass="App\Repository\PaypalTransactionRepository")
 * @ORM\HasLifecycleCallbacks()
 * @package                                                     App\Entity
 */
class PaypalTransaction
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
     * @ORM\Column(type="string")
     */
    private $transactionId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $paidAt;

    /**
     * @ORM\Column(type="boolean")
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
     * @ORM\PrePersist()
     */
    public function onPrePersist()
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
}
