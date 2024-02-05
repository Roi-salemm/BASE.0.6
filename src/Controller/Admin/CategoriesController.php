<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategoriesFormType;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin/categories', name: 'admin_categories_')]
class CategoriesController extends AbstractController
{

    // public function __construct(SerializerInterface $serializer)
    // {
    //     $this->serializer = $serializer;
    // }


    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findBy([], [
            'categoryOrder' => 'asc'
        ]);


        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ] 
        // compact('categories')
    );
    }



    #[Route('/ajout', name: 'add')]
    public function add(
        Request $request, 
        EntityManagerInterface $em, 
        CategoriesRepository $categoriesRepository): Response
    {
        //* Pour refuser les users avec d'autre role que ROLE_ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        //^^ Pour ajout :
        //* récuperation des champs d'entity ettendu 
        $newCategorie = new Categories();
      
        // dd($newCategorie);
        
        //* création du formulaire
        $categorieForm = $this->createForm(CategoriesFormType::class, $newCategorie);
        
   
        
        //* traite la requête du formulaire
        $categorieForm->handleRequest($request);
 

            //* réception du form :
            if(($categorieForm->isSubmitted()) && ($categorieForm->isValid())){
                //* -> La Base 
                
                $em->persist($newCategorie);
                $em->flush();
                $this->addFlash('success', 'Nouvelle Catégorie ajouté avec succès');
            
            // On redirige
            return $this->redirectToRoute('admin_categories_index');
            }

        return $this->renderForm('admin/categories/add.html.twig', [
            'categoriesForm' => $categorieForm->createView(),
        ]);
    }



    #[Route('/edition/{id}', name: 'edit')]
    public function edit(
        $id,
        Request $request, 
        EntityManagerInterface $em, 
        CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->find($id);

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        //* On divise le prix par 100
        // $prix = $product->getPrice() / 100;
        // $product->setPrice($prix);


        
        // On crée le formulaire
        $categoriesForm = $this->createForm(CategoriesFormType::class, $categories);
// dd($categories);
        // On traite la requête du formulaire
        $categoriesForm->handleRequest($request);

        //On vérifie si le formulaire est soumis ET valide
        if($categoriesForm->isSubmitted() && $categoriesForm->isValid()){
            // On récupère les images
            // $images = $categoriesForm->get('images')->getData();

            // foreach($images as $image){
            //     // On définit le dossier de destination
            //     $folder = 'products';

            //     // On appelle le service d'ajout
            //     $fichier = $pictureService->add($image, $folder, 300, 300);

            //     $img = new Images();
            //     $img->setName($fichier);
            //     $product->addImage($img);
            // }
            
            // On génère le slug
            // $slug = $slugger->slug($product->getName());
            // $product->setSlug($slug);

            // On arrondit le prix 
            // $prix = $product->getPrice() * 100;
            // $product->setPrice($prix);

            // On stocke
            $em->persist($categories);
            $em->flush();

            $this->addFlash('success', 'Categories modifié avec succès');

            //* On redirige
            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/categories/edit.html.twig',[
            'categoriesForm' => $categoriesForm->createView(),
            'categorie' => $categories
        ]);
    }




    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Categories $categorie, EntityManagerInterface $em, CategoriesRepository $categoriesRepository): Response
    {
        //* Verif de si l'utilisateur peut supprimer avec le voter
        $this->denyAccessUnlessGranted('ROLE_ADMIN', $categorie);




        if ($categorie->getProducts()->isEmpty()) {
            $em->remove($categorie);
            $em->flush();

            $this->addFlash('success', 'La catégorie a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'La catégorie ne peut pas être supprimée car elle est utilisée par des produits.');
        }

        $allCategories = $categoriesRepository->findBy([], [
            'categoryOrder' => 'asc'
        ]);


        return $this->render('admin/categories/index.html.twig', [
            'categories' => $allCategories
        ]);
    }






}