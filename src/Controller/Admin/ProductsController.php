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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $product = new Products();
        $productForm = $this->createForm(ProductsFormType::class, $product);
        $productForm->handleRequest($request);

        if($productForm->isSubmitted() && $productForm->isValid()){

            $images = $productForm->get('images')->getData();
            foreach($images as $image){
                $folder = 'products';
                $fichier = $pictureService->add($image, $folder, 300, 300);
                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            // $prix = $product->getPrice() * 100;
            // $product->setPrice($prix);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            return $this->redirectToRoute('admin_products_add');
        }

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

        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        $prix = $product->getPrice() / 100;
        $product->setPrice($prix);

        $productForm = $this->createForm(ProductsFormType::class, $product);

        $productForm->handleRequest($request);

        if($productForm->isSubmitted() && $productForm->isValid()){
            $images = $productForm->get('images')->getData();

            foreach($images as $image){
                $folder = 'products';
                $fichier = $pictureService->add($image, $folder, 300, 300);
                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }
            
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            // $prix = $product->getPrice() * 100;
            // $product->setPrice($prix);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

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
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);

        $product = $productsRepository->find($id);

        $em->remove($product);
        $em->flush();

        $allproducts = $productsRepository->findAll();

        $this->addFlash('success', 'Produit supprimé avec succès');

        return $this->render('admin/products/index.html.twig',[
            'produits' => $allproducts,
        ]);
    }



//! methods DELETE apparament necessaire pour le ajax
    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])){
            $nom = $image->getName();

            if($pictureService->delete($nom, 'products', 300, 300)){
                $em->remove($image);
                $em->flush();

                return new JsonResponse(['success' => true], 200);
            }
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