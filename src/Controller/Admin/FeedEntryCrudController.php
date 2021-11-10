<?php

namespace App\Controller\Admin;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
        $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ->disable(Action::BATCH_DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;

        return $actions;
    }



}
