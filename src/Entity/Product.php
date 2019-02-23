<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 20.02.19.
 * Time: 13:23
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class Product
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @package App\Entity
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Please, insert product name.")
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @Assert\NotBlank(message="Please, insert product price.")
     * @ORM\Column(type="decimal", scale=2)
     * @Assert\GreaterThan(
     *     value = 0
     * )
     */
    private $price;

    /**
     * @ORM\Column(type="string")
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private $visibility;

    /**
     * @ORM\Column(type="integer")
     */
    private $visibilityAdmin;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Please, upload the image.")
     * @Assert\File(mimeTypes={ "image/jpg", "image/jpeg" })
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(
     *     value = 0
     * )
     * @Assert\LessThan(
     *     value = 10000
     * )
     */
    private $availableQuantity;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return mixed
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $file
     */
    public function setImage($file)
    {
        $this->image = $file;
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
     * @param mixed $visibility
     */
    public function setVisibility($visibility): void
    {
        $this->visibility = $visibility;
    }

    /**
     * @return mixed
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return mixed
     */
    public function getAvailableQuantity()
    {
        return $this->availableQuantity;
    }

    /**
     * @param mixed $availableQuantity
     */
    public function setAvailableQuantity($availableQuantity): void
    {
        $this->availableQuantity = $availableQuantity;
    }

    /**
     * @return mixed
     */
    public function getVisibilityAdmin()
    {
        return $this->visibilityAdmin;
    }

    /**
     * @param mixed $visibilityAdmin
     */
    public function setVisibilityAdmin($visibilityAdmin): void
    {
        $this->visibilityAdmin = $visibilityAdmin;
    }
}