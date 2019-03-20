<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/20/19
 * Time: 11:36 AM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CouponCodes
 * @ORM\Entity(repositoryClass="App\Repository\CouponCodesRepository")
 * @package App\Entity
 */
class CouponCodes
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $codeName;

    /**
     * @ORM\Column(type="string")
     * @Assert\Regex(
     *     pattern     = "/^[0-9.%]+$/i",
     *     message     = "Only numbers, dot and percentage are allowed"
     *)
     */
    private $discount;

    /**
     * @ORM\Column(type="integer")
     */
    private $all;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
     */
    private $dateEnabled;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expireData;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Coupon")
     * @ORM\JoinColumn(nullable=false)
     */
    private $codeGroup;

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
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
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
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return $this->all;
    }

    /**
     * @param mixed $all
     */
    public function setAll($all): void
    {
        $this->all = $all;
    }

    /**
     * @return mixed
     */
    public function getCodeGroup()
    {
        return $this->codeGroup;
    }

    /**
     * @param mixed $codeGroup
     */
    public function setCodeGroup($codeGroup): void
    {
        $this->codeGroup = $codeGroup;
    }

    /**
     * @return mixed
     */
    public function getCodeName()
    {
        return $this->codeName;
    }

    /**
     * @param mixed $codeName
     */
    public function setCodeName($codeName): void
    {
        $this->codeName = $codeName;
    }

    /**
     * @return mixed
     */
    public function getDateEnabled()
    {
        return $this->dateEnabled;
    }

    /**
     * @param mixed $dateEnabled
     */
    public function setDateEnabled($dateEnabled): void
    {
        $this->dateEnabled = $dateEnabled;
    }

    /**
     * @return mixed
     */
    public function getExpireData()
    {
        return $this->expireData;
    }

    /**
     * @param mixed $expireData
     */
    public function setExpireData($expireData): void
    {
        $this->expireData = $expireData;
    }
}