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
             
            ->add('parent', options:[
                'label' => 'catégorie-parent'
            ])

            // ->add('parent', EntityType::class,[
            //     'class' => Categories::class,
            //     'label' => 'catégorie-parent',
            //     'choice_label' => 'parent',
            // ])



        //     ->add('categories', EntityType::class, [
        //         'class' => Categories::class,
        //         'choice_label' => 'name',
        //         'label' => 'Categorie',
                
        //         // 'group_by' => 'cat_parent.name', // Groupe par categorie parent
        //         // 'query_builder' => function(CategoriesRepository $cr)
        //         // {
        //         //     return $cr->createQueryBuilder('c')
        //         //     ->where('c.cat_parent')         // trie les category qui ont un parent 
        //         //     ->orderBy('c.name', 'ASC');             // Trie 
        //         // } 
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}





