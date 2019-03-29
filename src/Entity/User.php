<?php

namespace App\Entity;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @\Doctrine\ORM\Mapping\Entity(repositoryClass="App\Repository\UserRepository")
 * @\Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity
 *  (fields={"email"}, message="There is already an account with this email")
 */
class User extends AbstractController implements UserInterface
{
    /**
     * @\Doctrine\ORM\Mapping\Id()
     * @\Doctrine\ORM\Mapping\GeneratedValue()
     * @\Doctrine\ORM\Mapping\Column(type="integer")
     */
    private $id;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string", length=180, unique=true)
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     * @\Symfony\Component\Validator\Constraints\Email()
     */
    private $email;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="json")
     */
    private $roles = [];

    /**
     * @var                       string The hashed password
     * @\Doctrine\ORM\Mapping\Column(type="string")
     */
    private $password;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $firstName;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     */
    private $lastName;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     * @\Symfony\Component\Validator\Constraints\NotBlank()
     * @\Symfony\Component\Validator\Constraints\Regex(
     *     pattern     = "/^[0-9]+$/i",
     *     message     = "Only letters are allowed"
     *     )
     */
    private $phoneNumber;

    /**
     * @\Doctrine\ORM\Mapping\OneToMany(targetEntity="App\Entity\Wishlist",
     *      mappedBy="user", cascade={"persist","remove"})
     */
    private $wishlist;

    /**
     * @\Doctrine\ORM\Mapping\Column(type="string")
     */
    private $fullName;

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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {

        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER

        $roles[] = 'ROLE_USER';


        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }
}
