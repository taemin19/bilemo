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
                        'tags' => ['Token'],
                        'operationId' => 'postTokenItem',
                        'consumes' => $allowedFormat,
                        'produces' => $allowedFormat,
                        'summary' => 'Creates a JWT token',
                        'parameters' => [
                            [
                                'in' => 'body',
                                'name' => 'client',
                                'description' => 'Client credentials',
                                'properties' => [
                                    'username' => [
                                        'type' => 'string'
                                    ],
                                    'password' => [
                                        'type' => 'string'
                                    ]
                                ],
                                'example' => [
                                    'username' => 'client1',
                                    'password' => 'client1'
                                ]
                            ]
                        ],
                        'responses' => [
                            200 => [
                                'description' => 'JWT token created',
                                'schema' => [
                                    '$ref' => '#/definitions/Token-token:read'
                                ]
                            ],
                            401 => [
                                'description' => 'Bad credentials'
                            ]
                        ],
                    ]
                ]
            ],
            'definitions' => [
                'Token-token:read' => [
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
        return array_merge_recursive($officialDocumentation, $tokenDocumentation);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }
}
