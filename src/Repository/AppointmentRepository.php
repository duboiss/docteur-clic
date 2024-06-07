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
        return $this->findFutureAppointments('patient', $patient);
    }

    public function findPatientPastAppointments(User $patient): array
    {
        return $this->findPastAppointments('patient', $patient);
    }

    public function findDoctorFutureAppointments(User $doctor): array
    {
        return $this->findFutureAppointments('doctor', $doctor);
    }

    public function findDoctorPastAppointments(User $doctor): array
    {
        return $this->findPastAppointments('doctor', $doctor);
    }

    private function findFutureAppointments(string $role, User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere("a.$role = :user")
            ->andWhere('a.endsAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function findPastAppointments(string $role, User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere("a.$role = :user")
            ->andWhere('a.endsAt <= :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.startsAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
