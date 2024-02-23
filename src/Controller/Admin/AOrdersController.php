<?php

namespace App\Controller\Admin;








use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\Users;
use App\Form\OrdersType;
use App\Repository\OrdersDetailsRepository;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\DomPdf;
use App\Service\PdfService;
use Dompdf\Dompdf as DompdfDompdf;
use Dompdf\Options;
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

    #[Route('/{id}', name: 'show')]
    public function show(
    $id, 
    Request $request,
    OrdersDetailsRepository $ordersDetailsRepository, 
    UsersRepository $usersRepository, 
    EntityManagerInterface $em): Response
    {
        $userId = null;
        $orderDetails = $ordersDetailsRepository->findBy(
            ['orders' => $id]
        );
        $order = $em->getRepository(Orders::class)->find($id);
  

        foreach ($orderDetails as $oD) {
            $order = $oD->getOrders();
            $user = $order->getUsers();
            $userId = $user->getId();
        }

        $usersRepository = $em->getRepository(Users::class)->findBy(
            ['id' => $userId]
        );

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

    #[Route('/bill/{id}', name: 'bill')]
    public function bill(PdfService $pdf, 
    OrdersDetailsRepository $ordersDetailsRepository, 
    UsersRepository $usersRepository, 
    Request $request,
    $id,
    EntityManagerInterface $em)
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
        
        $html = $this->render('bill/index.html.twig', [
            '$orderDetails' => $orderDetails,
        ]);

    }

    #[Route('/pdf/{id}', name: 'pdf', methods: ["GET","POST"])]
    public function exportCommandes(Request $request, 
    PdfService $pdf,
    Orders $order,
    EntityManagerInterface $em, 
    OrdersDetailsRepository $ordersDetailsRepository,
    $id): Response
    {       
        $orderDetails = $ordersDetailsRepository->findBy(
            ['orders' => $id],
        );
        foreach ($orderDetails as $oD) {
            $order = $oD->getOrders();
            $user = $order->getUsers();
            $userId = $user->getId();
        }
        $usersRepository = $em->getRepository(Users::class)->findBy(
            ['id' => $userId]
        );
        $tva = 20;

        $html = $this->renderView('bill/index.html.twig', [
                '$orderDetails' => $orderDetails,
                'order' => $order,
                'orderDetails' => $orderDetails,
                'user' => $usersRepository,
                'tva' => $tva,
            ]);

        // $html .= '<link rel="stylesheet" href="/public/assets/css/fonctionality/bill.css">';
        // $html .= '<script src="/build/vendors~js/app.js"></script><script src="/build/runtime.js"></script><script src="/build/vendors-node_modules_popperjs_core_lib_index_js-node_modules_symfony_stimulus-bridge_dist_ind-f4bfca.js"></script>';
        $name = 'test';
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new DompdfDompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($name, array('Attachment' => 0));

        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }


}