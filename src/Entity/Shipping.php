<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 11:35 AM
 */

namespace App\Entity;

/**
 * Class Shipping
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\ShippingRepository")
 */
class Shipping
{
    /**
     * @\Doctrine\ORM\Mapping\Id @\Doctrine\ORM\Mapping\Column(type="string", unique=true)
     */
    private $country;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string", unique=true)
     */
    private $countryCode;

    /**
     * @\Symfony\Component\Validator\Constraints\NotBlank(message="Please, insert shipping price.")
     * @\Doctrine\ORM\Mapping\Column(type="decimal", scale=2, nullable=true)
     * @\Symfony\Component\Validator\Constraints\GreaterThan(
     *     value = 0
     * )
     * @\Symfony\Component\Validator\Constraints\Regex(
     *     pattern     = "/^[0-9.]+$/i",
     *     message     = "Only numbers and dot are allowed"
     * )
     */
    private $price;

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param mixed $countryCode
     */
    public function setCountryCode($countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }
}
