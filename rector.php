<?php

declare(strict_types=1);

use RectorPrefix20220126\Symplify\SymfonyPhpConfig\ValueObjectInliner;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;
use Rector\Core\Configuration\Option;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Nette\Set\NetteSetList;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {


    // $containerConfigurator->import(DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES);

    // $containerConfigurator->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    // $containerConfigurator->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    // $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    // $containerConfigurator->import(SymfonySetList::SYMFONY_STRICT);
    // $containerConfigurator->import(SymfonySetList::SYMFONY_60);

    // $containerConfigurator->import(NetteSetList::ANNOTATIONS_TO_ATTRIBUTES);

    $containerConfigurator->import(SetList::PHP_80);


    // $services = $containerConfigurator->services();
    // $services->set(AnnotationToAttributeRector::class)
    //     ->call('configure', [[
    //                              AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
    //                                  new AnnotationToAttribute(
    //                                      IsGranted::class,
    //                                      IsGranted::class
    //                                  ),
    //                              ]),
    //                          ]]);

    return;

    // get parameters
    // $parameters = $containerConfigurator->parameters();

    // // Define what rule sets will be applied
    // $containerConfigurator->import(SetList::DEAD_CODE);
    // $containerConfigurator->import(SetList::PHP_80);
    //
    // $services = $containerConfigurator->services();
    $services = $containerConfigurator->services();

    $services->set(UnionTypesRector::class);
    $services->set(StrContainsRector::class);

    $services->set(StrStartsWithRector::class);

    $services->set(StrEndsWithRector::class);

    $services->set(StringableForToStringRector::class);

    $services->set(AnnotationToAttributeRector::class);

    $services->set(ClassOnObjectRector::class);

    $services->set(GetDebugTypeRector::class);

    $services->set(TokenGetAllToObjectRector::class);

    $services->set(RemoveUnusedVariableInCatchRector::class);

    $services->set(ClassPropertyAssignToConstructorPromotionRector::class);

    $services->set(ChangeSwitchToMatchRector::class);
    // $services->set(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    //
    // $services->set(AnnotationToAttributeRector::class)
    //     ->call('configure', [[
    //                              AnnotationToAttributeRector::ANNOTATION_TO_ATTRIBUTE => ValueObjectInliner::inline([
    //                                  new AnnotationToAttribute(
    //                                      'Symfony\Component\Routing\Annotation\Route',
    //                                      'Symfony\Component\Routing\Annotation\Route'
    //                                  ),
    //                              ]),
    //                          ]]);

    // get services (needed for register a single rule)
    // $services = $containerConfigurator->services();

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};
