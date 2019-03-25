<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: inchoo
 * Date: 3/14/19
 * Time: 1:50 PM
 */

namespace App\Entity;

use App\Entity\User;
use App\Entity\Product;

/**
 * Class Wishlist
 *
 * @package                                                         App\Entity
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\WishlistRepository")
 */
class Wishlist
{
    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\Product", inversedBy="wishlist")
     */
    private $product;

    /**
     * @\Doctrine\ORM\Mapping\ManyToOne(targetEntity="App\Entity\User", inversedBy="wishlist")
     */
    private $user;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="smallint")
     */
    private $notify;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="smallint")
     */
    private $notified;

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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getNotify()
    {
        return $this->notify;
    }

    /**
     * @param mixed $notify
     */
    public function setNotify($notify): void
    {
        $this->notify = $notify;
    }

    /**
     * @return mixed
     */
    public function getNotified()
    {
        return $this->notified;
    }

    /**
     * @param mixed $notified
     */
    public function setNotified($notified): void
    {
        $this->notified = $notified;
    }
}
