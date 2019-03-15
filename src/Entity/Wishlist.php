<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/14/19
 * Time: 1:50 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Wishlist
 * @package App\Entity
 * @ORM\Entity(repositoryClass="App\Repository\WishlistRepository")
 */
class Wishlist
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="wishlist")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="wishlist")
     */
    private $user;

    /**
     * @ORM\Column(type="smallint")
     */
    private $notify;

    /**
     * @ORM\Column(type="smallint")
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