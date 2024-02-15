<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\Users;
use App\Form\OrdersType;
use App\Repository\OrdersDetailsRepository;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[Route('admin/orders', name: 'admin_orders_')]
class AOrdersController extends AbstractController
{
    private $ordersRepository;

    public function __construct(OrdersRepository $ordersRepository)
    {
        $this->ordersRepository = $ordersRepository;
    }

    #[Route('/index', name: 'index')]
    public function index(OrdersRepository $ordersRepository): Response
    { 
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $allOrders = $ordersRepository->findBy([], [
            'created_at' => 'desc'
        ]);

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $allOrders,
        ]);
    }


    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Orders();
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/orders/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Orders $order, 
    Users $users,
    $id, 
    OrdersDetailsRepository $ordersDetailsRepository, 
    UsersRepository $usersRepository, 
    EntityManagerInterface $em): Response
    {
        $orderDetails = $ordersDetailsRepository->findBy(
            ['orders' => $id]
        );

        
        foreach ($orderDetails as $oD) {
            $order = $oD->getOrders();
            $user = $order->getUsers();
            $userId = $user->getId();
        }
        $usersRepository = $em->getRepository(Users::class)->findBy(
            ['id' => $userId]
        );
        // dd($user);

        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'user' => $usersRepository,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Orders $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_orders_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/orders/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Orders $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_orders_index', [], Response::HTTP_SEE_OTHER);
    }
}