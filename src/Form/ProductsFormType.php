<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Products;
use App\Repository\CategoriesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Positive;
// use App\Validator\Constraints\ImageFile;

class ProductsFormType extends AbstractType
{  
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('name', options:[
                'label' => 'nom'
            ])
            ->add('description')
            
            //* MoneyType permet de arjouter le type de monay dans l'input et le passe en decimal 
            ->add('price', MoneyType::class, options:[
                'label' => 'prix',
                'divisor' => 100,
                //* Supper contrainte 
                'constraints' => [new Positive(
                    message: 'Le prix ne peut etre nagatif'
                    )]
                    ])
                    ->add('stock',options:[
                        'label' => 'Unité en stock'
                        ])
                        //* category vient d'une entity... 
                        ->add('categories', EntityType::class, [
                            'class' => Categories::class,
                            'choice_label' => 'name',
                            'label' => 'Categorie',
                            
                            // 'group_by' => 'cat_parent.name', // Groupe par categorie parent
                            // 'query_builder' => function(CategoriesRepository $cr)
                            // {
                            //     return $cr->createQueryBuilder('c')
                            //     ->where('c.cat_parent')         // trie les category qui ont un parent 
                            //     ->orderBy('c.name', 'ASC');             // Trie 
                            // }  
                            ])
                            ->add('images', FileType::class, [
                                'label' => false,
                                'multiple' => true,
                                'mapped' => false,
                                'required' => false, //? Choix lle sont en 'NULL' dans la base -> pour ne pas les doublé lors de la mododification de produits
                                // 'constraints' => [
                                    //     new All(
                                        //         new Image([
                                            //             'maxWidth' => 1280,
                                            //             'maxWidthMessage' => 'L\'image doit faire {{ max_width }} pixels de large au maximum'
                                            
                                            //         ])
                                            //     ),
                                            
                                            // ]
                                            ])
                                            ;
                                        }
                                      

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
