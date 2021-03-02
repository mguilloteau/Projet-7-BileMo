<?php

namespace App\Entity;

use App\Repository\PhoneRepository;
use Hateoas\Configuration\Annotation as Hateoas;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "details_phone",
 *          parameters = { "id" = "expr(object.getId())" },
 *   				absolute= true
 *      )
 * )
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 */
class Phone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
		 * @Assert\NotBlank()
		 * @Assert\Length(min=1)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
		 * @Assert\NotBlank()
		 * @Assert\Length(min=1)
     */
    private $color;

    /**
     * @ORM\Column(type="integer")
		 * @Assert\NotBlank()
		 * @Assert\Positive()
		 * @Assert\Length(min=1)
     */
    private $price;

    /**
     * @ORM\Column(type="text")
		 * @Assert\NotBlank()
		 * @Assert\Length(min=1)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
