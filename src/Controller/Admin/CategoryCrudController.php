<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom de la catégorie'),
            TextareaField::new('description', 'Description')->hideOnIndex(),

            // 🖼️ Upload d’image
            TextField::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),

            // 👀 Aperçu dans le tableau
            ImageField::new('imageName', 'Aperçu')
                ->setBasePath('/uploads/images/categories')
                ->onlyOnIndex(),
        ];
    }
}