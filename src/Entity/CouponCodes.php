<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: inchoo
 * Date: 3/20/19
 * Time: 11:36 AM
 */

namespace App\Entity;

/**
 * Class CouponCodes
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\CouponCodesRepository")
 * @package                                                            App\Entity
 */
class CouponCodes
{
    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string", length=255, unique=true)
     */
    private $codeName;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\Regex(
     *     pattern     = "/^[0-9.%]+$/i",
     *     message     = "Only numbers, dot and percentage are allowed"
     * )
     */
    private $discount;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $allProducts;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $categoryId;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $productId;


    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Coupon")
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=false)
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
