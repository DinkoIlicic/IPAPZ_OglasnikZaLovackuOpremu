<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: inchoo
 * Date: 3/20/19
 * Time: 9:26 AM
 */

namespace App\Entity;

/**
 * Class Coupon
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\CouponRepository")
 * @package                                                       App\Entity
 */
class Coupon
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
    private $codeGroupName;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\Regex(
     *     pattern     = "/^[0-9.%]+$/i",
     *     message     = "Only numbers, dot and percentage are allowed"
     * )
     */
    private $discount;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $codeGroupName
     */
    public function setCodeGroupName($codeGroupName): void
    {
        $this->codeGroupName = $codeGroupName;
    }

    /**
     * @return mixed
     */
    public function getCodeGroupName()
    {
        return $this->codeGroupName;
    }
}
