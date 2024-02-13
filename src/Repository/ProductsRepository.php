<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 *
 * @method Products|null find($id, $lockMode = null, $lockVersion = null)
 * @method Products|null findOneBy(array $criteria, array $orderBy = null)
 * @method Products[]    findAll()
 * @method Products[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

//    /**
//     * @return Products[] Returns an array of Products objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Products
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getAllProducts(): Array{
        $products = $this->createQueryBuilder('p'); 
        $requette = $products ->select('')     
        // ->select('p', 'c', 'i') // Sélectionne les entités Product (p), Category (c), et Image (i)
        ->leftJoin('p.categories', 'c') // Jointure avec Category (p.category)
        ->where('c.id = p.categories')
        // ->leftJoin('p.images', 'i') // Jointure avec Image (p.image)
        // ->andwhere('i.id = p.images')
        ->getQuery(); 
        // var_dump($requette);
        $result = $requette->getResult();
        return $result;
    }

    public function deleteProduct($id): Array{
        $products = $this->createQueryBuilder('p'); 
        $requette = $products ->select($id)     
        // ->select('p', 'c', 'i') // Sélectionne les entités Product (p), Category (c), et Image (i)
        ->leftJoin('p.images', 'i') // Jointure avec Category (p.category)
        ->where('i.id = p.images')
        // ->leftJoin('p.images', 'i') // Jointure avec Image (p.image)
        // ->andwhere('i.id = p.images')
        ->getQuery(); 
        // var_dump($requette);
        $result = $requette->getResult();
        return $result;
   }

   public function deleteProductWithImage($Id)
   {
       $entityManager = $this->getEntityManager();

       $product = $this->find($Id);

       if (!$product) {
           // Gérer le cas où le produit n'est pas trouvé
           return;
       }

       $image = $product->getImages();

       if ($image) {
           // Supprimer l'image associée au produit
           $entityManager->remove($image);
       }

       // Supprimer le produit lui-même
       $entityManager->remove($product);

       // Appliquer les changements dans la base de données
       $entityManager->flush();
   }



   public function findArticlesByCategory($categories, $limit)
   {
    return $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.categories', 'c') // Jointure avec la table Category sur le champ 'category' de Product
            ->where('c.id = $categories') // Filtrer par l'ID de catégorie spécifié
            ->setParameter('categories_id', $categories->getId())
            ->orderBy('p.id', 'DESC') // Tri par ID de produit en ordre décroissant pour obtenir les derniers produits
            ->setMaxResults($limit) // Limiter les résultats aux 4 premiers
            ->getQuery()
            ->getResult(); // Obtenir les résultats
    }

}
