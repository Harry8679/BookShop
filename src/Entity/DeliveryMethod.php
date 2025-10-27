<?php

namespace App\Entity;

use App\Repository\DeliveryMethodRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryMethodRepository::class)]
class DeliveryMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $price;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $carrier = null;

    #[ORM\Column]
    private bool $isActive = true;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): static { $this->price = $price; return $this; }
    public function getCarrier(): ?string { return $this->carrier; }
    public function setCarrier(?string $carrier): static { $this->carrier = $carrier; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function __toString(): string {
        return $this->name . " (".$this->price." €)";
    }
}