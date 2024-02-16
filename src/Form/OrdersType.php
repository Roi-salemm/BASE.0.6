<?php

namespace App\Form;

use App\Entity\Coupons;
use App\Entity\Orders;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $statusOptions = ['Enregistré', 'Expedié', 'Erreur', 'Autre'];

        $builder
            ->add('reference')
            // ->add('created_at')
            // ->add('coupons', EntityType::class, [
            //     'class' => Coupons::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('users', EntityType::class, [
            //     'class' => Users::class,
            //     'choice_label' => 'id',
            // ])
            ->add('status', ChoiceType::class, [
                'choices' => array_combine($statusOptions, $statusOptions), // Crée un tableau 'valeur' => 'libellé'
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
        ]);
    }
}
