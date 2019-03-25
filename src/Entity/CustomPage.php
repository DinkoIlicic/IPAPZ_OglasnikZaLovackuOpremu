<?php
/**
 * Created by PhpSt\Doctrine\ORM\Mapping.
 * User: inchoo
 * Date: 3/19/19
 * Time: 1:24 PM
 */

namespace App\Entity;

/**
 * Class CustomPage
 *
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\CustomPageRepository")
 * @package                                                           App\Entity
 */
class CustomPage
{
    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Symfony\Component\Validator\Constraints\Regex(
     *     pattern     = "/^[a-zA-Z0-9 _.-]+$/i",
     *     message     = "Only letters, numbers, space, underscore, dot and minus are allowed"
     *     )
     * @\Doctrine\ORM\Mapping\Column(type="string")
     */
    private $pageName;

    /**
     * @\Symfony\Component\Validator\Constraints\Length(
     *      max = 5000,
     *      maxMessage = "Content cannot be longer than {{ limit }} characters"
     * )
     * @\Doctrine\ORM\Mapping\Column(type="string", length=2000)
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $content;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="integer")
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
