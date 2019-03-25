<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: inchoo
 * Date: 3/13/19
 * Time: 11:04 AM
 */

namespace App\Entity;

/**
 * Class ProductCategory
 *
 * @package                                                                App\Entity
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\ProductCategoryRepository")
 */
class ProductCategory
{
    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Product", inversedBy="productCategory")
     */
    private $product;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Category", inversedBy="productCategory")
     */
    private $category;

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
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product): void
    {
        $this->product = $product;
    }
}
