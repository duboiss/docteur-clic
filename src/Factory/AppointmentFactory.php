<?php

namespace App\Factory;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Appointment>
 *
 * @method        Appointment|Proxy                     create(array|callable $attributes = [])
 * @method static Appointment|Proxy                     createOne(array $attributes = [])
 * @method static Appointment|Proxy                     find(object|array|mixed $criteria)
 * @method static Appointment|Proxy                     findOrCreate(array $attributes)
 * @method static Appointment|Proxy                     first(string $sortedField = 'id')
 * @method static Appointment|Proxy                     last(string $sortedField = 'id')
 * @method static Appointment|Proxy                     random(array $attributes = [])
 * @method static Appointment|Proxy                     randomOrCreate(array $attributes = [])
 * @method static AppointmentRepository|RepositoryProxy repository()
 * @method static Appointment[]|Proxy[]                 all()
 * @method static Appointment[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Appointment[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Appointment[]|Proxy[]                 findBy(array $attributes)
 * @method static Appointment[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Appointment[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class AppointmentFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        $startDate = new \DateTime('2024-01-01 07:00:00');
        $endDate = new \DateTime('2024-12-31 18:00:00');

        $randomDate = self::faker()->dateTimeBetween($startDate, $endDate);
        $randomHour = random_int(7, 16);
        $randomMinute = (int) floor($randomDate->format('i') / 10) * 10;

        $startsAt = \DateTimeImmutable::createFromMutable($randomDate);
        $startsAt = $startsAt->setTime($randomHour, $randomMinute);

        $mutableStartsAt = \DateTime::createFromImmutable($startsAt);
        $interval = new \DateInterval('PT' . random_int(1, 2) . 'H');
        $mutableEndsAt = clone $mutableStartsAt;
        $mutableEndsAt->add($interval);

        $endsAt = \DateTimeImmutable::createFromMutable($mutableEndsAt);

        return [
            'patient' => UserFactory::random(),
            'doctor' => UserFactory::random(['email' => 'doctor.doctor@gmail.com']),
            'startsAt' => $startsAt,
            'endsAt' => $endsAt,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Appointment $appointment): void {})
            ;
    }

    protected static function getClass(): string
    {
        return Appointment::class;
    }
}
