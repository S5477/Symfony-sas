<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideWhenCreating()->hideWhenUpdating(),
            TextField::new('author'),
            TextField::new('text'),
            TextField::new('email'),
            TextField::new('state'),
            ImageField::new('photoFilename')->setBasePath('/uploads/photos')->setUploadDir('/public/uploads/photos'),
            DateTimeField::new('createdAt')->hideWhenCreating()->hideWhenUpdating(),
            AssociationField::new('conference')->autocomplete()
        ];
    }
}
