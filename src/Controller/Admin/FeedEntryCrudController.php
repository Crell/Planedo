<?php

namespace App\Controller\Admin;

use App\Entity\FeedEntry;
use App\Message\RejectEntry;
use App\Message\RestoreEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class FeedEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FeedEntry::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Feed entry')
            ->setEntityLabelInPlural('Feed entries')
            ->setPaginatorPageSize(50)
            ->setDefaultSort(['dateModified' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title')->setDisabled(),
            TextField::new('link')->setDisabled(),
            DateTimeField::new('modified', 'Date')->setDisabled(),
            // @todo This contains HTML, so figure out how to format nicely.
            TextField::new('summary')->setDisabled()->onlyOnDetail(),
            TextField::new('feed.title', 'Feed')->setDisabled()->onlyOnIndex(),
//            AssociationField::new('feed')->setDisabled()->onlyOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $rejectEntry = Action::new('rejectEntry', 'Reject')
            ->displayAsLink()
            ->linkToCrudAction('rejectEntry')
            ->displayIf(static fn (FeedEntry $entry) => !$entry->getRejected());

        $restoreEntry = Action::new('restoreEntry', 'Restore')
            ->displayAsLink()
            ->linkToCrudAction('restoreEntry')
            ->displayIf(static fn (FeedEntry $entry) => $entry->getRejected());

        $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->disable(Action::BATCH_DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $rejectEntry)
            ->add(Crud::PAGE_INDEX, $restoreEntry)
        ;

        $rejectEntries = Action::new('rejectEntries', 'Reject')
            ->linkToCrudAction('batchRejectEntries')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-user-check')
        ;
        $restoreEntries = Action::new('restoreEntries', 'Restore')
            ->linkToCrudAction('batchRestoreEntries')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-user-check')
        ;

        $actions->addBatchAction($rejectEntries);
        $actions->addBatchAction($restoreEntries);

        return $actions;
    }

    public function batchRejectEntries(BatchActionDto $context, MessageBusInterface $bus): Response
    {
        $ids = $context->getEntityIds();
        foreach ($ids as $id) {
            $bus->dispatch(new RejectEntry($id));
        }

        $this->addFlash('notice', sprintf('%d entries rejected.', count($ids)));

        return $this->redirect($context->getReferrerUrl());
    }

    public function rejectEntry(AdminContext $context, MessageBusInterface $bus): Response
    {
        /** @var FeedEntry $entry */
        $entry = $context->getEntity()->getInstance();

        $bus->dispatch(new RestoreEntry($entry->getId()));

        $this->addFlash('notice', sprintf('Rejected entry: %s', $entry->getTitle()));

        return $this->redirect($context->getReferrer());
    }

    public function batchRestoreEntries(BatchActionDto $context, MessageBusInterface $bus): Response
    {
        $ids = $context->getEntityIds();
        foreach ($ids as $id) {
            $bus->dispatch(new RestoreEntry($id));
        }

        $this->addFlash('notice', sprintf('%d entries restored.', count($ids)));

        return $this->redirect($context->getReferrerUrl());
    }

    public function restoreEntry(AdminContext $context, MessageBusInterface $bus): Response
    {
        /** @var FeedEntry $entry */
        $entry = $context->getEntity()->getInstance();

        $bus->dispatch(new RestoreEntry($entry->getId()));

        $this->addFlash('notice', sprintf('Restored entry: %s', $entry->getTitle()));

        return $this->redirect($context->getReferrer());
    }

}
