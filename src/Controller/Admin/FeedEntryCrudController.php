<?php

namespace App\Controller\Admin;

use App\Entity\FeedEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FeedEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FeedEntry::class;
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
        $actions->disable(Action::NEW);

        return $actions;
    }


}
