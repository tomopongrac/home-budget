<?php

declare(strict_types=1);

namespace App\DBAL;

use App\Enum\TransactionType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class EnumTransactionType extends Type
{
    public const ENUM_TRANSACTION_TYPE = 'enum_transaction_type';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('ENUM("%s", "%s")', TransactionType::INCOME->value, TransactionType::EXPENSE->value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TransactionType
    {
        if (!is_string($value)) {
            return null;
        }

        return TransactionType::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof TransactionType ? $value->value : null;
    }

    public function getName(): string
    {
        return self::ENUM_TRANSACTION_TYPE;
    }
}
