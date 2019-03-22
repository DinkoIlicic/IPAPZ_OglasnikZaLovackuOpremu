<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/19/19
 * Time: 1:24 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CustomPage
 *
 * @ORM\Entity(repositoryClass="App\Repository\CustomPageRepository")
 * @package                                                           App\Entity
 */
class CustomPage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Regex(
     *     pattern     = "/^[a-zA-Z0-9 _.-]+$/i",
     *     message     = "Only letters, numbers, space, underscore, dot and minus are allowed"
     *     )
     * @ORM\Column(type="string")
     */
    private $pageName;

    /**
     * @Assert\Length(
     *      max = 5000,
     *      maxMessage = "Content cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=2000)
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private $visibilityAdmin;


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
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * @param mixed $pageName
     */
    public function setPageName($pageName): void
    {
        $this->pageName = $pageName;
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
