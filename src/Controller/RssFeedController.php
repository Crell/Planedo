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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RssFeedController extends AbstractController
{
    public function __construct(
        protected FeedEntryRepository $repository,
    ) {}

    #[Route('/rss', name: 'rss_main')]
    public function index(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->repository->latestEntriesPaginator($offset);

        $selfLink = $this->generateUrl('rss_main', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

        $feed = new Feed();
        $feed->setTitle('Planedo');
        $feed->setDateModified(time());
        $feed->setId($selfLink);
        $feed->setDescription('Description goes here.');
        $feed->setFeedLink($selfLink, 'rss');
        $feed->setLink($selfLink, 'rss');
        // @todo Unclear how to set next/prev links on Atom feed.

        foreach ($paginator as $record) {
            $feed->addEntry($this->makeEntry($feed, $record));
        }

        $out = $feed->export('rss');

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
        $entry->setDateModified($record->getDateModified());
        $entry->setLink($record->getLink());
        if ($summary = $record->getDescription()) {
            $entry->setDescription($summary);
        }

        return $entry;
    }
}
