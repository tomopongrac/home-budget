<?php

namespace App\Validator;

use App\Entity\Category;
use App\Entity\User;
use App\Exception\ApiValidationException;
use App\Repository\CategoryRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CategoryBelongsToAuthenticatedUserValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Security $security,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var CategoryBelongsToAuthenticatedUser $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $category = $this->categoryRepository->find($value);
        if (!$category instanceof Category) {
            throw new ApiValidationException(['Category not found']);
        }

        if ($user !== $category->getUser()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
