<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'patientAppointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['appointment'])]
    #[MaxDepth(1)]
    private ?User $patient = null;

    #[ORM\ManyToOne(inversedBy: 'doctorAppointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['appointment'])]
    #[MaxDepth(1)]
    private ?User $doctor = null;

    #[ORM\Column]
    #[Groups(['appointment'])]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column]
    #[Groups(['appointment'])]
    private ?\DateTimeImmutable $endsAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getDoctor(): ?User
    {
        return $this->doctor;
    }

    public function setDoctor(?User $doctor): static
    {
        $this->doctor = $doctor;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateEndsAt(): void
    {
        if (null === $this->endsAt && null !== $this->startsAt) {
            $mutableEndsAt = \DateTime::createFromImmutable($this->startsAt);
            $mutableEndsAt->modify('+1 hour');
            $this->endsAt = \DateTimeImmutable::createFromMutable($mutableEndsAt);
        }
    }

    public function isPast(): bool
    {
        return $this->endsAt < new \DateTimeImmutable();
    }

    public function isUpcoming(): bool
    {
        return new \DateTimeImmutable() < $this->startsAt;
    }
}
