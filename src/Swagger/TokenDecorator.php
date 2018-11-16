<?php

namespace App\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TokenDecorator implements NormalizerInterface
{
    /**
     * @var NormalizerInterface
     */
    private $decorated;

    /**
     * TokenDecorator constructor.
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
        $allowedFormat = ['application/json'];
        $tokenDocumentation =[
            'paths' => [
                '/api/login_check' => [
                    'post' => [
                        'tags' => ['Authentication'],
                        'operationId' => 'postTokenItem',
                        'consumes' => $allowedFormat,
                        'produces' => $allowedFormat,
                        'summary' => 'Generates a JWT token',
                        'parameters' => [
                            [
                                'in' => 'body',
                                'name' => 'client',
                                'description' => 'Client credentials',
                                'example' => [
                                    'username' => 'client1',
                                    'password' => 'client1'
                                ]
                            ]
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'Client access token',
                                'schema' => [
                                    '$ref' => '#/definitions/Token'
                                ]
                            ],
                            400 => [
                                'description' => 'Invalid input'
                            ],
                            401 => [
                                'description' => 'Bad credentials'
                            ]
                        ],
                    ]
                ]
            ],
            'definitions' => [
                'Token' => [
                    'type' => 'object',
                    'description' => "",
                    'properties' => [
                        'token' => [
                            'type' => 'string',
                            'readOnly' => true,
                        ]
                    ]
                ]
            ]
        ];
        $officialDocumentation = $this->decorated->normalize($object, $format, $context);
        return array_merge_recursive($tokenDocumentation, $officialDocumentation);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
