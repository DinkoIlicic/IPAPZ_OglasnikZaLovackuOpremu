<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:23
 */

namespace App\Entity;

/**
 * Class Seller
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\SellerRepository")
 * @package                                                       App\Entity
 * @\Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity
 *  (fields={"user"}, message="This user is already applied for seller")
 */
class Seller
{
    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Doctrine\ORM\Mapping\OneToOne(targetEntity="App\Entity\User")
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     * @\Doctrine\ORM\Mapping\Column(type="string")
     */
    private $applyContent;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
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
