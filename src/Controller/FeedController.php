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

class FeedController extends AbstractController
{
    public function __construct(
        protected FeedEntryRepository $repository,
    ) {}

    #[Route('/atom', name: 'atom_main')]
    public function atomFeed(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));

        return $this->makeFeed(
            offset: $offset,
            selfRoute: 'atom_main',
            feedType: 'atom',
            contentType: 'text/plain',
        //contentType: 'application/atom+xml',
        );
    }

    #[Route('/rss', name: 'rss_main')]
    public function rssFeed(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));

        return $this->makeFeed(
            offset: $offset,
            selfRoute: 'rss_main',
            feedType: 'rss',
            contentType: 'text/plain',
            //contentType: 'application/rss+xml',
        );
    }

    protected function makeFeed(int $offset, string $selfRoute, string $feedType, string $contentType): Response
    {
        //$offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->repository->latestEntriesPaginator($offset);

        $selfLink = $this->generateUrl($selfRoute, referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

        $feed = new Feed();
        $feed->setTitle('Planedo');
        $feed->setDateModified(time());
        $feed->setId($selfLink);
        $feed->setDescription('Description goes here.');
        $feed->setFeedLink($selfLink, $feedType);
        $feed->setLink($selfLink, $feedType);
        // @todo Unclear how to set next/prev links on feeds.

        foreach ($paginator as $record) {
            $feed->addEntry($this->makeEntry($feed, $record));
        }

        $out = $feed->export($feedType);

        return new Response(
            content: $out,
            headers: [
//                'content-type' => 'application/atom+xml'
                'content-type' => $contentType
            ]
        );
    }

    protected function makeEntry(Feed $feed, FeedEntry $record): Entry
    {
        $entry = $feed
            ->createEntry()
            ->setTitle($record->getTitle())
            ->setDateModified($record->getDateModified())
            ->setLink($record->getLink())
        ;
        if ($summary = $record->getDescription()) {
            $entry->setDescription($summary);
        }

        return $entry;
    }
}
