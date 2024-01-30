<?php

namespace App\Form;


use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


// use App\Validator\Constraints\ImageFile;



class CategoriesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //* Ajout des champs d'imput. (in base : id, name, parent(self), categoryOrder)
        $builder
            ->add('name', options:[
                'label' => 'sous-categories'
            ])

            ->add('categoryOrder', options:[
                'label' => 'Ordre d\'affichage',
            ]) 
             
            ->add('cat_parent', options:[
                'label' => 'catégorie-parent'
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





