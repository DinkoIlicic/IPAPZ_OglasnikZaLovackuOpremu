<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/27/19
 * Time: 12:35 PM
 */

namespace App\Entity;

/**
 * Class UserAddress
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\UserAddressRepository")
 * @package                                                     App\Entity
 */
class UserAddress
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
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $country;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $addressFirst;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    private $addressSecond;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $city;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $state;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $postalCode;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getCountry()
    {
        return $this->country;
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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getAddressFirst()
    {
        return $this->addressFirst;
    }

    /**
     * @param mixed $addressFirst
     */
    public function setAddressFirst($addressFirst): void
    {
        $this->addressFirst = $addressFirst;
    }

    /**
     * @return mixed
     */
    public function getAddressSecond()
    {
        return $this->addressSecond;
    }

    /**
     * @param mixed $addressSecond
     */
    public function setAddressSecond($addressSecond)
    {
        $this->addressSecond = $addressSecond;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $postalCode
     */
    public function setPostalCode($postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }
}
