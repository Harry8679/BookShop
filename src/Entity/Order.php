<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private string $status = 'pending';

    #[ORM\ManyToOne]
    private ?Address $shippingAddress = null;

    #[ORM\ManyToOne]
    private ?DeliveryMethod $deliveryMethod = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $total = 0.00;

    public function __construct() { $this->items = new ArrayCollection(); }

    public function getId(): ?int { return $this->id; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getShippingAddress(): ?Address { return $this->shippingAddress; }
    public function setShippingAddress(?Address $shippingAddress): static { $this->shippingAddress = $shippingAddress; return $this; }

    public function getDeliveryMethod(): ?DeliveryMethod { return $this->deliveryMethod; }
    public function setDeliveryMethod(?DeliveryMethod $deliveryMethod): static { $this->deliveryMethod = $deliveryMethod; return $this; }

    /** @return Collection<int, OrderItem> */
    public function getItems(): Collection { return $this->items; }
    public function addItem(OrderItem $item): static {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }
        return $this;
    }
    public function removeItem(OrderItem $item): static {
        if ($this->items->removeElement($item) && $item->getOrder() === $this) {
            $item->setOrder(null);
        }
        return $this;
    }

    public function getTotal(): float { return $this->total; }
    public function setTotal(float $total): static { $this->total = $total; return $this; }

    public function __toString(): string {
        return "Commande #".$this->id;
    }
}