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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

// use App\Validator\Constraints\ImageFile;



class CategoriesFormType extends AbstractType
{
    private $categoriesRepository;

    public function __construct(CategoriesRepository $categoriesRepository)
    {
        $this->categoriesRepository = $categoriesRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //* Ajout des champs d'imput. (in base : id, name, parent(self), categoryOrder)
        $builder
          
            // ->add('name', ChoiceType::class, [
            //     'label' => 'Sous-catégories',
            //     'choices' => $this->getExistingCategoryNames(),
            //     'required' => false,
            //     'multiple' => true, // Si vous souhaitez permettre la sélection de plusieurs catégories
            //     'expanded' => true, // Si vous souhaitez afficher les options sous forme de liste d'input au lieu d'une liste déroulante
            // ])

            // ->add('nameExisting', HiddenType::class,[
            //     'label' => 'sous-categories existante',
            //     'choice_label' => 'name',
            //     'query_builder' => $this->categoriesRepository->findDistinctCategoriesQueryBuilderName(),
            //     'expanded' => true,
            //     'multiple' => false,
            //     'required' => false,
            // ])
            // ->add('existingCategoryValue', HiddenType::class, [
            //     'data' => '', // Valeur par défaut pour le champ HiddenType
            // ])

            ->add('name', options:[
                'label' => 'Nouvelle sous-categories'
            ])






            ->add('categoryOrder', options:[
                'label' => 'Ordre d\'affichage',
                'help' => 'Permet d\'ordonner les categories selon votre preferance. Peut rester vide' 
            ]) 
             




            ->add('parent', options:[
                'label' => 'catégorie-parent',
            ])

            // ->add('parent', EntityType::class,[
            //     'class' => Categories::class,
            //     'label' => 'catégorie-parent',
            //     'choice_label' => 'parent',
            //     'query_builder' => $this->categoriesRepository->findDistinctCategoriesQueryBuilder(),
            // ])

            //     ->add('parent', ChoiceType::class, [
            //     'label' => 'catégorie-parent',
            //     'choices' => $this->getExistingCategoryNames(),
            //     'required' => false,
            //     'multiple' => true, // Si vous souhaitez permettre la sélection de plusieurs catégories
            //     'expanded' => true, // Si vous souhaitez afficher les options sous forme de liste d'input au lieu d'une liste déroulante
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


    private function getExistingCategoryNames()
    {
        $categories = $this->categoriesRepository->findAll();

        $choices = [];

        foreach ($categories as $category) {
            $choices[$category->getParent()] = $category->getParent();
        }

        return $choices;
    }

    
}





