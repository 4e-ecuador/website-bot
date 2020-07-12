<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\Api\GetCite;

/**
 * @ApiResource(
 *     collectionOperations={},
 *     itemOperations={
 *         "get_cite"={
 *             "method"="GET",
 *             "path"="/cites/random",
 *             "controller"=GetCite::class,
 *             "openapi_context"=Cite::API_GET_CITE_CONTEXT,
 *             "read"=false
 *         }
 *     },
 *     normalizationContext={"groups"={"me:read"}}
 * )
 */
class Cite
{
    public const API_GET_CITE_CONTEXT
        = [
            'summary'     => 'Collection of warrior cites.',
            'description' => 'Retrieves a random cite.',
            'parameters'  => [],
            'responses'   => [
                '200' => [
                    'description' => 'Random warrior cite.',
                    'content'     => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ],

        ];
}
