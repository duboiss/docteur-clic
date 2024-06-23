<?php

namespace App\Service;

use App\Entity\User;

class AppointmentService
{
    public function getFullCalendarDoctorAppointments(User $doctor): array
    {
        $doctorAppointments = [];

        foreach ($doctor->getDoctorAppointments() as $appointment) {
            $startsAt = $appointment->getStartsAt()->format('Y-m-d\TH:i:s');
            $endsAt = $appointment->getEndsAt()->format('Y-m-d\TH:i:s');

            $doctorAppointments[] = [
                'title' => 'RDV',
                'start' => $startsAt,
                'end' => $endsAt,
            ];
        }

        return $doctorAppointments;
    }
}
