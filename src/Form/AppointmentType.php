<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Validator\TimeConstraints;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Créneau désiré',
                'widget' => 'single_text',
                'html5' => false, // Disable native html picker
                'attr' => ['class' => 'js-datetime-picker'],
                'constraints' => [
                    new TimeConstraints(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
