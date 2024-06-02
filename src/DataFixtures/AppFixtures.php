<?php

namespace App\DataFixtures;

use App\Factory\AppointmentFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createMany(15);
        UserFactory::createOne([
            'roles' => ['ROLE_USER'],
            'email' => 'user.user@gmail.com',
            'password' => 'demo'
        ]);
        UserFactory::createOne([
            'roles' => ['ROLE_DOCTOR'],
            'email' => 'doctor.doctor@gmail.com',
            'password' => 'demo'
        ]);
        UserFactory::createOne([
            'roles' => ['ROLE_ADMIN'],
            'email' => 'admin.admin@gmail.com',
            'password' => 'demo'
        ]);

        foreach (UserFactory::all() as $user) {
            if (in_array('ROLE_DOCTOR', $user->getRoles(), true)) {
                continue;
            }
            AppointmentFactory::createMany(
                random_int(2, 6),
                ['patient' => $user, 'doctor' => UserFactory::random(['email' => 'doctor.doctor@gmail.com'])]
            );
        }

        $manager->flush();
    }
}
