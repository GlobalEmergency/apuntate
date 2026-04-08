<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Services\Normalizer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FlattenExceptionNormalizer implements NormalizerInterface
{
    public function __construct(
        private string $appEnv = 'prod',
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
    {
        $data = [
            'status' => $object->getStatusCode(),
            'message' => $object->getMessage(),
        ];

        if ('dev' === $this->appEnv || 'test' === $this->appEnv) {
            $data['class'] = $object->getClass();
            $data['file'] = $object->getFile();
            $data['line'] = $object->getLine();
        }

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FlattenException;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            FlattenException::class => true,
        ];
    }
}
