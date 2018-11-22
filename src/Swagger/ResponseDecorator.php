<?php

namespace App\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ResponseDecorator implements NormalizerInterface
{
    /**
     * @var NormalizerInterface
     */
    private $decorated;

    /**
     * ResponseDecorator constructor.
     * @param NormalizerInterface $decorated
     */
    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        $unauthorizedResponse = [
            'description' => 'Authorization required'
        ];

        $forbiddenResponse = [
          'description' => 'Access is not granted'
        ];

        // add custom responses
        $docs['paths']['/api/products']['get']['responses'][401] = $unauthorizedResponse;
        $docs['paths']['/api/products/{id}']['get']['responses'][401] = $unauthorizedResponse;
        $docs['paths']['/api/users']['get']['responses'][401] = $unauthorizedResponse;
        $docs['paths']['/api/users']['post']['responses'][401] = $unauthorizedResponse;
        $docs['paths']['/api/users/{id}']['get']['responses'][401] = $unauthorizedResponse;
        $docs['paths']['/api/users/{id}']['get']['responses'][403] = $forbiddenResponse;
        $docs['paths']['/api/users/{id}']['delete']['responses'][401] = $unauthorizedResponse;
        $docs['paths']['/api/users/{id}']['delete']['responses'][403] = $forbiddenResponse;

        return $docs;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
