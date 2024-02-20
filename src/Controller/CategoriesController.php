<?php

namespace App\Controller;

// use App\Repository\CategoriesRepository;
use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// #[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{

    // ^^ categoriesList
    #[Route('/categoriesList', name: 'categoriesList')]
    public function list( $slugName, CategoriesRepository $categoriesRepository): Response
    {
        $category = $categoriesRepository->findBy([], ['categoryOrder' => 'asc']);

        return $this->render('categories/list.html.twig', [
            'categories' => $category,
            'slugName' => $slugName,
            ]);

    }


}
