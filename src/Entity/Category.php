<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: dinko
 * Date: 20.02.19.
 * Time: 12:32
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Category
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @package                                                         App\Entity
 * @\Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity
 *  (fields={"name"}, message="This category name is already used")
 */
class Category
{
    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     */
    private $name;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\User")
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $visibility;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $visibilityAdmin;

    /**
     * @\Doctrine\ORM\Mapping\OneToMany
     *  (targetEntity="App\Entity\ProductCategory", mappedBy="category", cascade={"persist","remove"})
     */
    private $productCategory;

    public function __construct()
    {
        $this->productCategory = new ArrayCollection();
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
    public function getVisibility()
    {
        return $this->visibility;
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

    /**
     * @return mixed
     */
    public function getProductCategory()
    {
        return $this->productCategory;
    }
}
