<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Theme;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }


    public function findLastEvents()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('e')
            ->from('App\Entity\Event', 'e')
            ->orderBy('e.dateCreation', 'DESC')
            ->setMaxResults(6);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findEventsByTheme(Theme $theme)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('e')
            ->from('App\Entity\Event', 'e')
            ->where('e.theme = :theme')
            ->setParameter('theme', $theme)
            ->orderBy('e.dateCreation', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function PaginationQuery()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'ASC')
            ->getQuery();
    }


    public function findEventWithParticipants($eventId)
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'p') // Sélectionner l'événement et ses participants
            ->leftJoin('e.participants', 'p') // Joindre la relation des participants
            ->where('e.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getOneOrNullResult();
    }

/**
     * Trouver des événements qui se chevauchent pour un utilisateur dans une plage horaire spécifiée.
     *
     * @param User $user
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return array
     */
    public function findOverlappingEvents(User $user, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.dateDebut < :end')
            ->andWhere('e.dateFin > :start')
            ->setParameter('start', $end)
            ->setParameter('end', $start)
            ->getQuery();

        return $qb->getResult();
    }
    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
