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

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;

    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'participants')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $participant;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Event::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $events;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'users')]
    // #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $favoris;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'favoris')]
    // #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Avis::class)]
    private Collection $avis;

    // #[ORM\OneToMany(mappedBy: 'user', targetEntity: Event::class)]
    // #[ORM\JoinColumn(onDelete: 'CASCADE')]
    // private Collection $events;

    /*****************finir le systeme de favoris **********************/


    public function __construct()
    {
        $this->participant = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->avis = new ArrayCollection();
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
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
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


    public function isEqualTo(User $otherUser): bool
    {
        return $this->getId() === $otherUser->getId();
    }

    //     // Comparez les ID des utilisateurs
    //     return $this->id === $user->getId();
    // }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }
    public function addFavori(User $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->addFavori($this);
        }

        return $this;
    }

    public function removeFavori(self $favori): static
    {
        $this->favoris->removeElement($favori);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    // public function getUsers(): Collection
    // {
    //     return $this->users;
    // }

    // public function addUser(self $user): static
    // {
    //     if (!$this->users->contains($user)) {
    //         $this->users->add($user);
    //     }

    //     return $this;
    // }

    // public function removeUser(self $user): static
    // {
    //     $this->users->removeElement($user);

    //     return $this;
    // }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setUser($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getUser() === $this) {
                $avi->setUser(null);
            }
        }

        return $this;
    }




    // /**
    //  * @return Collection<int, Favoris>
    //  */
    public function getUserFavoris(): Collection
    {
        return $this->userFavoris;
    }

    public function addUserFavori(Favoris $userFavori): static
    {
        if (!$this->userFavoris->contains($userFavori)) {
            $this->userFavoris->add($userFavori);
            $userFavori->addUser($this);
        }

        return $this;
    }

    public function removeUserFavori(Favoris $userFavori): static
    {
        if ($this->userFavoris->removeElement($userFavori)) {
            $userFavori->removeUser($this);
        }

        return $this;
    }
}
