<?php

namespace App\Controller;

use App\Repository\FeedEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HtmlFeedController extends AbstractController
{
    public function __construct(
        protected FeedEntryRepository $repository,
    ) {}

    #[Route('/', name: 'html_main')]
    public function index(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->repository->latestEntriesPaginator($offset);

        return $this->render('html_feed/index.html.twig', [
            'controller_name' => 'HtmlFeedController',
            'entries' => $paginator,
            'previous' => $offset - FeedEntryRepository::ItemsPerPage,
            'next' => min(count($paginator), $offset + FeedEntryRepository::ItemsPerPage),
        ]);
    }
}
