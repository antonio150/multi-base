<?php 

namespace App\Controller\Main;

use App\Entity\Main\Site;
use App\Form\SiteForm;
use App\Service\DatabaseSwitcher;
use App\Service\DynamicEntityManagerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/superadmin/sites')]
final class SiteController extends AbstractController
{
    private DatabaseSwitcher $databaseSwitcher;
    private DynamicEntityManagerProvider $dynamicEntityManagerProvider;
    public function __construct(
        DatabaseSwitcher $databaseSwitcher,
        DynamicEntityManagerProvider $dynamicEntityManagerProvider,
        )
    {
        $this->databaseSwitcher = $databaseSwitcher;
        $this->dynamicEntityManagerProvider = $dynamicEntityManagerProvider;
    }

    private function form(
        Request                $request,
        Site                  $site,
        EntityManagerInterface $entityManager,
    ): Response
    {
        /** @var User $currentuser */
        $currentuser = $this->getUser();

        $isView = $request->query->get('view', false);

        $form = $this->createForm(SiteForm::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $sitRaison = $site->getSitRaisonsociale();            
            $sitAdress = $site->getSitAdresse();            
            $sitTel = $site->getSitTel();            
            $sitMail = $site->getSitMail();            
            $sitCode = $site->getSitCode();            
            $sitBddNom = $site->getSitBddNom();            
            $sitBddUser = $site->getSitBddUser();            
            $sitBddMdp = $site->getSitBddMdp();  
            
            // 1. Vérifs éventuelles (à adapter si utile)
            $errors = [];
            $existingSitBddNom = $entityManager->getRepository(Site::class)
                ->findOneBy(['sitBddNom' => $sitBddNom]);
            if ($existingSitBddNom && $existingSitBddNom->getId() !== $site->getId()) {
                $errors[] = "nom de base de données";
            }
            $existingRaisonsociale = $entityManager->getRepository(Site::class)->findOneBy(['sitRaisonsociale' => $sitRaison]);
            if ($existingRaisonsociale && $existingRaisonsociale->getId() !== $site->getId()) {
                $errors[] = "raison sociale";
            }
            $existingSitMail = $entityManager->getRepository(Site::class)->findOneBy(['sitMail' => $sitMail ?? null]);
            if ($existingSitMail && $existingSitMail->getId() !== $site->getId()) {
                $errors[] = "adresse mail";
            }
            $existingSitCode =  $entityManager->getRepository(Site::class)->findOneBy(['sitCode' => $sitCode ?? null]);
            if ($existingSitCode && $existingSitCode->getId() !== $site->getId()) {
                $errors[] = "code de site";
            }
            if (!empty($errors)) {
                $this->addFlash('error', 'Les valeurs suivantes existent déjà : ' . implode(', ', $errors));
                return $this->render('espace_admin/site/form.html.twig', [
                    'site' => $site,
                    'form' => $form->createView(),
                    'isView' => false,
                ]);
            }
        
            $this->databaseSwitcher->createDatabase($sitBddNom, $sitBddUser, $sitBddMdp);


            $entityManager->persist($site);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('espace_admin/site/form.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
            'isView' => false,
        ]);
    }
    
    #[Route('/', name: 'app_site_index', methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager
    ): Response
    {
        $sites = $entityManager->getRepository(Site::class)->findAll();

        return $this->render('espace_admin/site/index.html.twig', [
            'sites' => $sites,
        ]);
    }

    #[Route('/new', name: 'app_site_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $site = new Site();
        return $this->form($request, $site, $entityManager);

    }

    #[Route('/show/{id}', name: 'app_site_show', methods: ['GET'])]
    public function show(Site $site): Response
    {
        $form = $this->createForm(SiteForm::class, $site, ['disabled' => true]);

        return $this->render('espace_admin/site/form.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
            'isView' => true,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request                $request,
        Site                  $site,
        EntityManagerInterface $entityManager
    ): Response
    {
        return $this->form($request, $site, $entityManager);
    }


    #[Route('/{id}/delete', name: 'app_site_delete', methods: ['POST', 'GET'])]
    public function delete(
        Request                $request,
        Site                  $site,
        EntityManagerInterface $entityManager
    ): Response
    {
        /** @var User $currentuser */
        $currentuser = $this->getUser();
       
        if ($site->isEstActif()) {
            // if ($this->isDeletable($permi, $entityManager)) {
                $site->setEstActif(false);
            // }
        } else {
            $site->setEstActif(true);
        }

        $entityManager->persist($site);
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }
}