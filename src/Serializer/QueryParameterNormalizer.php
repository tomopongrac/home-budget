<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Dto\Transaction\TransactionDataAggregationFilterParameters;
use App\Dto\Transaction\TransactionFilterParameters;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class QueryParameterNormalizer implements DenormalizerInterface
{
    public function __construct(
        private ObjectNormalizer $normalizer,
    ) {
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if (is_array($data) && isset($data['categories'])) {
            $data['categories'] = explode(',', $data['categories']);
        }

        return $this->normalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return TransactionFilterParameters::class === $type || TransactionDataAggregationFilterParameters::class === $type;
    }
}
