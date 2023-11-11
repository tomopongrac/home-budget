<?php

namespace App\Security\Voter;

use App\Entity\Transaction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TransactionVoter extends Voter
{
    public const EDIT = 'TRANSACTION_EDIT';
    public const VIEW = 'TRANSACTION_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW], true)
            && $subject instanceof \App\Entity\Transaction;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Transaction $transaction */
        $transaction = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($transaction, $user),
            self::EDIT => $this->canEdit($transaction, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Transaction $trasaction, UserInterface $user): bool
    {
        return $user === $trasaction->getCategory()?->getUser();
    }

    private function canEdit(Transaction $trasaction, UserInterface $user): bool
    {
        return $user === $trasaction->getCategory()?->getUser();
    }
}
