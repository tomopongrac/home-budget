<?php

namespace App\Serializer;

use App\Entity\Transaction;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TransactionDateNormalizer implements NormalizerInterface
{
    public function __construct(
        private ObjectNormalizer $normalizer,
    ) {
    }

    public function normalize($object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context['datetime_format'] = 'Y-m-d';
        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Transaction;
    }
}