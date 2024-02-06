<?php

namespace App\Controller;


use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MainController extends AbstractController
{
    //^^ Premiére page du site qui affiche une presentation sommaire des categories et produits 
    #[Route('/', name: 'app_main')]
    public function index(CategoriesRepository $categoriesRepository, ProductsRepository $productsRepository): Response
    {

        $cat = $categoriesRepository->findBy([], ['categoryOrder' => 'asc']);
        $pro = $productsRepository->findBy([], ['name' => 'asc']);

        $imagePrincipal = '9b3f3e466fa7206d31d8eb64648659fb.webp';
        // var_dump($cat);

        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'categories' => $cat,
            'products' => $pro,
            'imagePrincipal' => $imagePrincipal,
            
        ]);
    }

    // && Trois boules de chargement avec gsap
    #[Route('/loader', name: 'app_loader')]
    public function loader(): Response
    {

        return $this->render('fonctionality/loader.html.twig',[
            'loader' => 'loader',
        ]);
    }

    // && card 3d qui bouge au contact de la souris avzec gsap
    #[Route('/card', name: 'app_card')]
    public function card(): Response
    {

        return $this->render('fonctionality/card.html.twig',[
            'loader' => 'loader',
        ]);
    }


    // && Menu nav-1 (menu standard sans fond)
    #[Route('/nav-1', name: 'app_nav-1')]
    public function nav1(): Response
    {

        return $this->render('menu/nav-1.html.twig',[
            'loader' => 'loader',
        ]);
    }


}





