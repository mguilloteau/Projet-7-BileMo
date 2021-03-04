<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Hateoas\Configuration\Annotation as Hateoas;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "details_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *   				absolute= true
 *      ),
 *   exclusion = @Hateoas\Exclusion(groups={"list_users"})
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "update_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *   				absolute= true
 *      ),
 *   exclusion = @Hateoas\Exclusion(groups={"list_users"})
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "delete_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *   				absolute= true
 *      ),
 *   exclusion = @Hateoas\Exclusion(groups={"list_users"})
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
		 * @Serializer\Groups({"list_users"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
		 * @Assert\NotBlank(message="This field can't be blank")
		 * @Assert\Length(
		 *   min="6",
		 *   minMessage="This field must contain a minimum of 6 characters",
		 *   max="18",
		 *   maxMessage="This field must contain a maximum of 18 characters"
		 * )
		 * @Serializer\Groups({"list_users"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
		 * @Assert\NotBlank(message="This field can't be blank")
		 * @Serializer\Groups({"list_users"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
		 * @Assert\NotBlank(message="This field can't be blank")
		 * @Serializer\Groups({"list_users"})
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=255)
		 * @Assert\NotBlank(message="This field can't be blank")
		 * @Assert\Email(
		 *     message = "The email '{{ value }}' is not a valid email."
		 * )
		 * @Serializer\Groups({"list_users"})
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
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

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
