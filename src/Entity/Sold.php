<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: dinko
 * Date: 21.02.19.
 * Time: 08:05
 */

namespace App\Entity;

/**
 * Class Sold
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\SoldRepository")
 * @\Doctrine\ORM\Mapping\HasLifecycleCallbacks()
 * @package                                                     App\Entity
 */
class Sold
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
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Product")
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     * @\Symfony\Component\Validator\Constraints\GreaterThan(
     *     value = 0
     * )
     * @\Symfony\Component\Validator\Constraints\LessThan(
     *     value = 100
     * )
     */
    private $quantity;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="datetime")
     */
    private $boughtAt;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="decimal", scale=2)
     */
    private $price;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string", length=255)
     */
    private $couponCodeName;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\Regex(
     *     pattern     = "/^[0-9.%]+$/i",
     *     message     = "Only numbers, dot and percentage are allowed"
     * )
     */
    private $discount;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="decimal", scale=2)
     */
    private $totalPrice;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="boolean")
     */
    private $confirmed;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="decimal", scale=2)
     */
    private $afterDiscount;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    private $paymentMethod;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\UserAddress")
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="decimal", scale=2)
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    private $shippingPrice;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="decimal", scale=2)
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     */
    private $toPay;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product): void
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getBoughtAt()
    {
        return $this->boughtAt;
    }

    /**
     * @param mixed $boughtAt
     */
    public function setBoughtAt($boughtAt): void
    {
        $this->boughtAt = $boughtAt;
    }

    /**
     * @\Doctrine\ORM\Mapping\PrePersist()
     */
    public function onPrePersist()
    {
        $this->boughtAt = new \DateTime('now');
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
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
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param mixed $totalPrice
     */
    public function setTotalPrice($totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @return mixed
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param mixed $discount
     */
    public function setDiscount($discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return mixed
     */
    public function getCouponCodeName()
    {
        return $this->couponCodeName;
    }

    /**
     * @param mixed $couponCodeName
     */
    public function setCouponCodeName($couponCodeName): void
    {
        $this->couponCodeName = $couponCodeName;
    }

    /**
     * @return mixed
     */
    public function getAfterDiscount()
    {
        return $this->afterDiscount;
    }

    /**
     * @param mixed $afterDiscount
     */
    public function setAfterDiscount($afterDiscount): void
    {
        $this->afterDiscount = $afterDiscount;
    }

    /**
     * @param mixed $paymentMethod
     */
    public function setPaymentMethod($paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getShippingPrice()
    {
        return $this->shippingPrice;
    }

    /**
     * @param mixed $shippingPrice
     */
    public function setShippingPrice($shippingPrice): void
    {
        $this->shippingPrice = $shippingPrice;
    }

    /**
     * @return mixed
     */
    public function getToPay()
    {
        return $this->toPay;
    }

    /**
     * @param mixed $toPay
     */
    public function setToPay($toPay): void
    {
        $this->toPay = $toPay;
    }
}
