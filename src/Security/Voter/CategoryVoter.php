<?php

namespace App\Security\Voter;

use App\Entity\Category;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CategoryVoter extends Voter
{
    public const EDIT = 'CATEGORY_EDIT';
    public const VIEW = 'CATEGORY_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW], true)
            && $subject instanceof \App\Entity\Category;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Category $category */
        $category = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($category, $user),
            self::EDIT => $this->canEdit($category, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Category $category, UserInterface $user): bool
    {
        return $user === $category->getUser();
    }

    private function canEdit(Category $category, UserInterface $user): bool
    {
        return $user === $category->getUser();
    }
}
