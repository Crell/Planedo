<?php

namespace App\Controller;

use App\Entity\FeedEntry;
use App\Repository\FeedEntryRepository;
use Laminas\Feed\Writer\Entry;
use Laminas\Feed\Writer\Feed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AtomFeedController extends AbstractController
{
    public function __construct(
        protected FeedEntryRepository $repository,
    ) {}

    #[Route('/atom', name: 'atom_main')]
    public function index(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->repository->latestEntriesPaginator($offset);

        $selfLink = $this->generateUrl('atom_main');

        $feed = new Feed();
        $feed->setTitle('Planedo');
        $feed->setDateModified(time());
        $feed->setId($selfLink);
        $feed->setFeedLink($selfLink, 'atom');
        // @todo Unclear how to set next/prev links on Atom feed.

        foreach ($paginator as $record) {
            $feed->addEntry($this->makeEntry($feed, $record));
        }

        $out = $feed->export('atom');

        return new Response(
            content: $out,
            headers: [
//                'content-type' => 'application/atom+xml'
                'content-type' => 'text/plain'
            ]
        );
    }

    protected function makeEntry(Feed $feed, FeedEntry $record): Entry
    {
        $entry = $feed->createEntry();
        $entry->setTitle($record->getTitle());
        $entry->setDateModified($record->getModified());
        $entry->setLink($record->getLink());
        if ($summary = $record->getSummary()) {
            $entry->setDescription($summary);
        }

        return $entry;
    }
}
