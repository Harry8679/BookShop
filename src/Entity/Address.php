<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $street;

    #[ORM\Column(length: 20)]
    private string $postalCode;

    #[ORM\Column(length: 100)]
    private string $city;

    #[ORM\Column(length: 100)]
    private string $country = 'France';

    #[ORM\Column(options: ['default' => false])]
    private bool $isBilling = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $isShipping = false;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    private ?User $user = null;

    public function getId(): ?int { return $this->id; }
    public function getStreet(): string { return $this->street; }
    public function setStreet(string $street): static { $this->street = $street; return $this; }
    public function getPostalCode(): string { return $this->postalCode; }
    public function setPostalCode(string $postalCode): static { $this->postalCode = $postalCode; return $this; }
    public function getCity(): string { return $this->city; }
    public function setCity(string $city): static { $this->city = $city; return $this; }
    public function getCountry(): string { return $this->country; }
    public function setCountry(string $country): static { $this->country = $country; return $this; }
    public function isBilling(): bool { return $this->isBilling; }
    public function setIsBilling(bool $isBilling): static { $this->isBilling = $isBilling; return $this; }
    public function isShipping(): bool { return $this->isShipping; }
    public function setIsShipping(bool $isShipping): static { $this->isShipping = $isShipping; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function __toString(): string {
        return $this->street . " " . $this->postalCode . " " . $this->city;
    }
}
