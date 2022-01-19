<?php

namespace App\Controller\Admin;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use App\Entity\User;
use App\Repository\FeedEntryRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    protected readonly FeedRepository $feedRepo;
    protected readonly FeedEntryRepository $entryRepo;

    public function __construct(
        protected readonly AdminUrlGenerator $routeBuilder,
        EntityManagerInterface $em,
    ) {
        $this->feedRepo = $em->getRepository(Feed::class);
        $this->entryRepo = $em->getRepository(FeedEntry::class);
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->redirect($this->routeBuilder->setController(FeedCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Planedo');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Feeds'),
            MenuItem::linkToCrud('Feeds', 'fa fa-tags', Feed::class)
                ->setBadge($this->feedRepo->getActiveFeedCount()),
            MenuItem::linkToCrud('Feed Entries', 'fa fa-file-text', FeedEntry::class)
                ->setBadge($this->entryRepo->getApprovedEntryCount()),

            MenuItem::section('Users'),
//            MenuItem::linkToCrud('Comments', 'fa fa-comment', Comment::class),
            MenuItem::linkToCrud('Users', 'fa fa-user', User::class),
        ];
    }
}
