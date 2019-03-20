<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/20/19
 * Time: 9:26 AM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Coupon
 * @ORM\Entity(repositoryClass="App\Repository\CouponRepository")
 * @package App\Entity
 */
class Coupon
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
    private $codeGroupName;

    /**
     * @ORM\Column(type="string")
     * @Assert\Regex(
     *     pattern     = "/^[0-9.%]+$/i",
     *     message     = "Only numbers, dot and percentage are allowed"
     *)
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
