<?php

namespace App\Controller\Admin;

use App\Entity\FeedEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('link')->setDisabled(),
            TextField::new('title')->setDisabled(),
            // @todo Display the name of the Feed, if we can?
        ];
    }


    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::NEW);

        return $actions;
    }



}
