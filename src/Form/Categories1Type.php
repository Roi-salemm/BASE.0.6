<?php

namespace App\Form;

use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\CategoriesRepository;

class Categories1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('categoryOrder')
            ->add('categories', EntityType::class, [
                'class' => Categories::class,
                // 'choice_label' => 'name',
                'label' => 'Catégorie',
                // 'group_by' => 'parent.name',
                // 'query_builder' => function(CategoriesRepository $cr){
                //     return $cr->createQueryBuilder('c')
                //         ->where('c.parent IS NOT NULL')
                //         ->orderBy('c.name', 'ASC');
                // }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
