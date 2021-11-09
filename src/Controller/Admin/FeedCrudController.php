<?php

namespace App\Controller\Admin;

use App\Entity\Feed;
use App\Message\UpdateFeed;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Messenger\MessageBusInterface;

class FeedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feed::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */

    public function configureActions(Actions $actions): Actions
    {
        $updateFeed = Action::new('updateFeed', 'Update')
            ->displayAsLink()
            ->linkToCrudAction('updateFeed');;

        $actions->add(Crud::PAGE_EDIT, $updateFeed);
        $actions->add(Action::INDEX, $updateFeed);

        return $actions;
    }

    public function updateFeed(AdminContext $context, MessageBusInterface $bus)
    {
        /** @var Feed $feed */
        $feed = $context->getEntity()->getInstance();

        $bus->dispatch(new UpdateFeed($feed->getId()));

        $this->addFlash('notice', sprintf('Feed %s (%d) queued for updating', $feed->getTitle(), $feed->getId()));

        return $this->redirect($context->getReferrer());
    }

}
