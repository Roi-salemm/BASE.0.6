<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;

#[Route('/produits', name: 'produits_')]
class ProductsController extends AbstractController
{
    // ^^ indexProduits
    #[Route('/index', name: 'vitrine')]
    public function index(Categories $categories, ProductsRepository $productsRepository): Response
    {

        $pro = $productsRepository->findBy([], ['name' => 'asc']);

        // dd($pro);
      
        // $session = $this->requestStack->getSession();
        // dd($pro);
        // dd($session);
        // dd($_SESSION);

        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'products' => $pro, 
            'categories' => $categories,
            'image1' => '7725e4e8fc79d81eac26114250f4fe18.webp',
            'image2' => 'fb1743aae1d4c2a2dedb036f5a5137df.webp',
        ]);
    }
   
    // ^^ produitsDetails
    #[Route('{id}', name: 'details')]
    public function details(Products $product, ProductsRepository $productsRepository, ): Response
    {
        // $pro = $productsRepository->find('id');

        //TODO Au moment de l'affichage produit le prix n'est pas identique a celuis en base de données
        return $this->render('products/details.html.twig', [
            // 'product' => $pro,
            // 'categories' => $categories,
            'product' => $product,
            // 'name' => $productsRepository->findAll()

        ]);
    }


    //&& TEST produitsDetails
    #[Route('/truc{slug}', name: 'test')]
    public function test(Products $product, Categories $categories, ProductsRepository $ProductsRepository): Response
    {
        return $this->render('products/details.html.twig', [
        ]);
    }



    // ^^ produitsList
    #[Route('/list/{slug}', name: 'produitsList')]
    public function list(ProductsRepository $productsRepository, $slug){

        $pro = $productsRepository->findBy([], ['name' => 'asc']);

        return $this->render('products/details.html.twig', [
            'controller_name' => 'ProductsController',
            'product' => $pro,
        ]);
    }


}
