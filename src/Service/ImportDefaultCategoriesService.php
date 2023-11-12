<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ImportDefaultCategoriesService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function importFor(User $user): void
    {
        foreach (Category::DEFAULT_CATEGORIES as $defaultCategory) {
            $category = (new Category())
                ->setName($defaultCategory)
                ->setUser($user);

            $this->entityManager->persist($category);
        }

        $this->entityManager->flush();
    }
}
