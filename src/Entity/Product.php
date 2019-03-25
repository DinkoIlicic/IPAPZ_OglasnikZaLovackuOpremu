<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: dinko
 * Date: 20.02.19.
 * Time: 13:23
 */

namespace App\Entity;

use App\Entity\Comment;
use App\Entity\ProductCategory;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Product
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\ProductRepository")
 * @package                                                        App\Entity
 */
class Product
{

    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Symfony\Component\Validator\Constraints\NotBlank(message="Please, insert product name.")
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\Regex(
     *     pattern     = "/^[a-zA-Z0-9 _.-]+$/i",
     *     message     = "Only letters, numbers, space, underscore, dot and minus are allowed"
     * )
     */
    private $name;

    /**
     * @\Doctrine\ORM\Mapping\OneToMany
     *  (targetEntity="App\Entity\ProductCategory", mappedBy="product", cascade={"persist","remove"})
     */
    private $productCategory;

    /**
     * @\Doctrine\ORM\Mapping\OneToMany
     *  (targetEntity="App\Entity\Wishlist", mappedBy="product", cascade={"persist","remove"})
     */
    private $wishlist;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\User")
     * @\Doctrine\ORM\Mapping\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @\Symfony\Component\Validator\Constraints\NotBlank(message="Please, insert product price.")
     * @\Doctrine\ORM\Mapping\Column(type="decimal",       scale=2)
     * @\Symfony\Component\Validator\Constraints\GreaterThan(
     *     value = 0
     * )
     */
    private $price;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     */
    private $content;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $visibility;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $visibilityAdmin;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\NotBlank(message="Please, upload the image.")
     * @\Symfony\Component\Validator\Constraints\File(mimeTypes={          "image/jpg", "image/jpeg" })
     * @\Symfony\Component\Validator\Constraints\Image(
     *     minWidth = 300,
     *     maxWidth = 2000,
     *     minHeight = 300,
     *     maxHeight = 2000
     * )
     */
    private $image;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     * @\Symfony\Component\Validator\Constraints\GreaterThanOrEqual(
     *     value = 0
     * )
     * @\Symfony\Component\Validator\Constraints\LessThan(
     *     value = 10000
     * )
     */
    private $availableQuantity;

    /**
     * @\Doctrine\ORM\Mapping\OneToMany
     *  (targetEntity="App\Entity\Comment", mappedBy="product", cascade={"persist", "remove"})
     * @\Doctrine\ORM\Mapping\OrderBy({"createdAt"="DESC"})
     */
    private $comments;

    /**
     * @\Symfony\Component\Validator\Constraints\Regex(
     *     pattern     = "/^[a-zA-Z0-9 _.-]+$/i",
     *     message     = "Only letters, numbers, space, underscore, dot and minus are allowed"
     *     )
     * @\Doctrine\ORM\Mapping\Column(name="custom_url", type="string", length=255)
     */
    private $customUrl;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->productCategory = new ArrayCollection();
        $this->wishlist = new ArrayCollection();
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
     * @return ArrayCollection|Category[]
     */
    public function getProductCategory()
    {
        $categories = new ArrayCollection();
        foreach ($this->productCategory as $prod) {
            /**
             * @var ProductCategory $prod
             */
            $categories[] = $prod->getCategory();
        }

        return $categories;
    }

    /**
     * @param ArrayCollection|ProductCategory $productCategory
     */
    public function setProductCategory(ArrayCollection $productCategory)
    {
        foreach ($productCategory as $category) {
            /**
             * @var ProductCategory $newProductCategory
             */
            $newProductCategory = new ProductCategory();
            $newProductCategory->setProduct($this);
            $newProductCategory->setCategory($category);
            $this->productCategory[] = $newProductCategory;
        }
    }

    /**
     * @return mixed
     */
    public function getWishlist()
    {
        return $this->wishlist;
    }

    /**
     * @param mixed $wishlist
     */
    public function setWishlist($wishlist): void
    {
        $this->wishlist = $wishlist;
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


    /**
     * @return \Doctrine\Common\Collections\Collection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param  Comment $comment
     * @return $this
     */
    public function addComment(Comment $comment)
    {
        if (!$this->comments->contains($comment)) {
            $comment->setProduct($this);
            $this->comments[] = $comment;
        }

        return $this;
    }

    /**
     * @param  Comment $comment
     * @return $this
     */
    public function removeComment(Comment $comment)
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            if ($comment->getProduct() === $this) {
                $comment->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomUrl()
    {
        return $this->customUrl;
    }

    /**
     * @param $customUrl
     */
    public function setCustomUrl($customUrl)
    {
        $this->customUrl = $customUrl;
    }
}
