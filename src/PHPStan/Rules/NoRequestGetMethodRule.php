<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
final class NoRequestGetMethodRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        if ($node->name->name !== 'get') {
            return [];
        }

        $callerType = $scope->getType($node->var);

        $requestType = new ObjectType('Symfony\Component\HttpFoundation\Request');

        if (!$requestType->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Call to deprecated method Request::get(). ' .
                'Use $request->query->get() for GET parameters, ' .
                '$request->request->get() for POST parameters, ' .
                'or $request->attributes->get() for route attributes.'
            )
                ->identifier('symfony.deprecatedRequestGet')
                ->build(),
        ];
    }
}
