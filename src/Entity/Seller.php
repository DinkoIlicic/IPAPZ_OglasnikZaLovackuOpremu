<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:23
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Seller
 * @ORM\Entity(repositoryClass="App\Repository\SellerRepository")
 * @package App\Entity
 * @UniqueEntity(fields={"user"}, message="This user is already applied for seller")
 */
class Seller
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string")
     */
    private $applyContent;

    /**
     * @ORM\Column(type="integer")
     */
    private $verified;

    /**
     * @return mixed
     */
    public function getApplyContent()
    {
        return $this->applyContent;
    }

    /**
     * @param mixed $applyContent
     */
    public function setApplyContent($applyContent): void
    {
        $this->applyContent = $applyContent;
    }

    /**
     * @return mixed
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * @param mixed $verified
     */
    public function setVerified($verified): void
    {
        $this->verified = $verified;
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
}
