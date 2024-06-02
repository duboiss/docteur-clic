<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findPatientFutureAppointments(User $patient): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.patient = :patient')
            ->andWhere('a.endsAt > :now')
            ->setParameter('patient', $patient)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPatientPastAppointments(User $patient): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.patient = :patient')
            ->andWhere('a.endsAt <= :now')
            ->setParameter('patient', $patient)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.startsAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
