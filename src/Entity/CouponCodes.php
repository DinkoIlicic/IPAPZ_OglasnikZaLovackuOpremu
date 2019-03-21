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
    private $allProducts;

    /**
     * @ORM\Column(type="integer")
     */
    private $categoryId;

    /**
     * @ORM\Column(type="integer")
     */
    private $productId;


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
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     */
    public function setProductId($productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId($categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return mixed
     */
    public function getAllProducts()
    {
        return $this->allProducts;
    }

    /**
     * @param mixed $allProducts
     */
    public function setAllProducts($allProducts): void
    {
        $this->allProducts = $allProducts;
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
}