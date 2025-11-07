<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Gedmo\Slug(fields: ["name"])]
    #[ORM\Column(length:255, unique:true)]
    private ?string $slug = null;

    #[ORM\Column(type:Types::TEXT, nullable:true)]
    private ?string $description = null;

    #[ORM\Column(type:"decimal", precision:10, scale:2)]
    private ?string $price = null;

    #[ORM\Column(type:"decimal", precision:10, scale:2, nullable:true)]
    private ?string $promoPrice = null;

    #[ORM\Column(type:"integer")]
    private ?int $stock = 0;

    #[ORM\Column(type:"boolean")]
    private bool $isPublished = true;

    #[ORM\Column(length:255, nullable:true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping:"product_images", fileNameProperty:"image")]
    private ?File $imageFile = null;

    #[ORM\Column(type:"datetime")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type:"datetime", nullable:true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy:"products")]
    #[ORM\JoinColumn(nullable:false)]
    private ?Category $category = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): ?string { return $this->slug; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getPrice(): ?float { return $this->price !== null ? (float)$this->price : null; }
    public function setPrice(float $price): static { $this->price = $price; return $this; }

    public function getPromoPrice(): ?float { return $this->promoPrice !== null ? (float)$this->promoPrice : null; }
    public function setPromoPrice(?float $promoPrice): static { $this->promoPrice = $promoPrice; return $this; }

    public function getStock(): ?int { return $this->stock; }
    public function setStock(int $stock): static { $this->stock = $stock; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): static { $this->image = $image; return $this; }

    public function isPublished(): ?bool { return $this->isPublished; }
    public function setIsPublished(bool $isPublished): static { $this->isPublished = $isPublished; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): static { $this->category = $category; return $this; }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getImageFile(): ?File { return $this->imageFile; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }

    public function getPriceFormatted(): string
    {
        $value = $this->promoPrice !== null ? (float)$this->promoPrice : (float)$this->price;
        return number_format($value, 2, ',', ' ') . ' €';
    }
}
