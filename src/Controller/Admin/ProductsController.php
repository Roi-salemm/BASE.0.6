<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ImagesRepository;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();
        return $this->render('admin/products/index.html.twig', 
        compact('produits')
        );
    }

    #[Route('/ajout', name: 'add')]
    public function add(
        Request $request, 
        EntityManagerInterface $em, 
        SluggerInterface $slugger,  
        ProductsRepository $productsRepository, 
        PictureService $pictureService): Response
    {
        //* Pour refuser les users avec d'autre role que ROLE_ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        

        //^^ Pour ajout :
        //* crée un "nouveau produit"
        $product = new Products();

        //* crée le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);
       
        //^^ traite la requête du formulaire
        $productForm->handleRequest($request);

        //* si le formulaire est soumis && valide
        if($productForm->isSubmitted() && $productForm->isValid()){
            //^^ On récupère les images
            $images = $productForm->get('images')->getData();
            
            foreach($images as $image){
                // On définit le dossier de destination
                $folder = 'products';
                // On appelle le service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);
                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            //* On génère le slug pour spliter() le prompt d'input
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            // On arrondit le prix 
            // $prix = $product->getPrice() * 100;
            // $product->setPrice($prix);

            // On stocke
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            // On redirige
            return $this->redirectToRoute('admin_products_add');
        }

// dd($productForm);  

        // return $this->render('admin/products/add.html.twig',[
        //     'productForm' => $productForm->createView()
        // ]);

        return $this->renderForm('admin/products/add.html.twig', compact('productForm'));
        // ['productForm' => $productForm]
    }



    #[Route('/edition/{id}', name: 'edit')]
    public function edit(
        $id,
        Request $request, 
        EntityManagerInterface $em, 
        SluggerInterface $slugger, 
        PictureService $pictureService,
        ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->find($id);
        // dd($product);

        //* Verif de si l'utilisateur peut editer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        //* On divise le prix par 100
        $prix = $product->getPrice() / 100;
        $product->setPrice($prix);

        // On crée le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        // On traite la requête du formulaire
        $productForm->handleRequest($request);

        //On vérifie si le formulaire est soumis ET valide
        if($productForm->isSubmitted() && $productForm->isValid()){
            // On récupère les images
            $images = $productForm->get('images')->getData();

            foreach($images as $image){
                // On définit le dossier de destination
                $folder = 'products';

                // On appelle le service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }
            
            // On génère le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            // On arrondit le prix 
            // $prix = $product->getPrice() * 100;
            // $product->setPrice($prix);

            // On stocke
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            //* On redirige
            return $this->redirectToRoute('admin_products_index');
        }

        return $this->render('admin/products/edit.html.twig',[
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }


    #[Route('/suppression/{id}', name: 'delete')]
    public function delete($id, Products $product, 
    ProductsRepository $productsRepository,
    EntityManagerInterface $em,
    ImagesRepository $imagesRepository ): Response
    {
        //* Verif de si l'utilisateur peut supprimer avec le voter
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);

        // $deleteProduct = $productsRepository->deleteProduct($id);

        
        $product = $productsRepository->find($id);
        // $image = $imagesRepository->find($id);

        // dd($image);

        $em->remove($product);
        $em->flush();

        $allproducts = $productsRepository->findAll();

        // $productsRepository->deleteProductWithImage($id);
        $this->addFlash('success', 'Produit supprimé avec succès');
    

        return $this->render('admin/products/index.html.twig',[
            'produits' => $allproducts,
        ]);
    }



//! methods DELETE apparament necessaire pour le ajax
    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        // On récupère le contenu de la requête
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])){
            // Si le token csrf est valide
            //* On récupère le nom de l'image
            $nom = $image->getName();

            if($pictureService->delete($nom, 'products', 300, 300)){
                //* supprime l'image de la base de données
                $em->remove($image);
                $em->flush();

                //* le ajax 
                return new JsonResponse(['success' => true], 200);
            }
            // La suppression a échoué
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }




    #[Route('/availability', name: 'availability')]
    public function updateAvailability(Request $request, EntityManagerInterface $em): Response
    {
        $productId = $request->request->get('id');
        $availability = $request->request->get('availability');
        $product = $em->getRepository(Products::class)->find($productId);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé pour l\'ID : '.$productId);
        }

        $product->setAvailability($availability);
        $em->flush();

        return new Response('La disponibilité a été mise à jour avec succès.');
    }



}