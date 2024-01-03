<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDeCreation = null;

    #[ORM\ManyToOne(inversedBy: 'themes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'theme', targetEntity: Event::class, orphanRemoval: true)]
    private Collection $events;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Picture $pictures = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDateDeCreation(): ?\DateTimeInterface
    {
        return $this->dateDeCreation;
    }

    public function setDateDeCreation(\DateTimeInterface $dateDeCreation): static
    {
        $this->dateDeCreation = $dateDeCreation;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setTheme($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getTheme() === $this) {
                $event->setTheme(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getPictures(): ?Picture
    {
        return $this->pictures;
    }

    public function setPictures(Picture $pictures): static
    {
        $this->pictures = $pictures;

        return $this;
    }
}
