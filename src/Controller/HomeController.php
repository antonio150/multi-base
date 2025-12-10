<?php

namespace App\Controller;

use App\Entity\Main\SuperUser;
use App\Service\DataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        EntityManagerInterface $entityManager
       
    ): Response
    {
     
        return $this->redirectToRoute('app_site_index');
    }
}
