<?php

namespace App\Entity;

use App\Entity\Event;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Event::class, orphanRemoval: true)]
    private Collection $event;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    private Collection $participant;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'favoritedByUsers')]
    #[JoinTable('user_favorite_event')]
    private Collection $likes;

    #[ORM\ManyToMany(targetEntity: Event::class, inversedBy: 'favoritedByUsers')]
    #[ORM\JoinTable(name: 'user_favorite_event')]
    private $favoriteEvents;


    public function __construct()
    {
        $this->event = new ArrayCollection();
        $this->participant = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->favoriteEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->event->contains($event)) {
            $this->event->add($event);
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->event->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getParticipant(): Collection
    {
        return $this->participant;
    }

    public function addParticipant(Event $participant): static
    {
        if (!$this->participant->contains($participant)) {
            $this->participant->add($participant);
            $participant->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipant(Event $participant): static
    {
        if ($this->participant->contains($participant)) {
            $this->participant->removeElement($participant);
        }

        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Event $event): self
    {
        if (!$this->likes->contains($event)) {
            $this->likes[] = $event;
            $event->addFavoritedByUser($this); // Assurez-vous que cette méthode est définie dans votre classe Event
        }

        return $this;
    }

    public function removeLike(Event $event): self
    {
        $this->likes->removeElement($event);
        $event->removeFavoritedByUser($this); // Assurez-vous que cette méthode est définie dans votre classe Event

        return $this;
    }
    
    public function getFavoriteEvents(): Collection
    {
        return $this->favoriteEvents;
    }

    public function addFavoriteEvent(Event $event): self
    {
        if (!$this->favoriteEvents->contains($event)) {
            $this->favoriteEvents[] = $event;
            $event->addFavoritedByUser($this);
        }

        return $this;
    }

    public function removeFavoriteEvent(Event $event): self
    {
        if ($this->favoriteEvents->removeElement($event)) {
            $event->removeFavoritedByUser($this);
        }

        return $this;
    }

        public function isEqualTo(User $otherUser): bool
    {
        return $this->getId() === $otherUser->getId();
    }

    //     // Comparez les ID des utilisateurs
    //     return $this->id === $user->getId();
    // }



}
