<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class GDPRUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ...
            ->add(
                'game-log',
                FileType::class,
                [
                    'label'       => 'game_log.tsv',

                    // unmapped means that this field is not associated to any entity property
                    'mapped'      => false,

                    // make it optional so you don't have to re-upload the PDF file
                    // every time you edit the Product details
                    'required'    => false,

                    // unmapped fields can't define their validation using annotations
                    // in the associated entity, so you can use the PHP constraint classes
                    // 'constraints' => [
                    //     new File(
                    //         [
                    //             'maxSize'          => '1024k',
                    //             'mimeTypes'        => [
                    //                 'application/pdf',
                    //                 'application/x-pdf',
                    //             ],
                    //             'mimeTypesMessage' => 'Please upload a valid PDF document',
                    //         ]
                    //     ),
                    // ],
                ]
            )// ...
        ;
    }

    // public function configureOptions(OptionsResolver $resolver)
    // {
    //     $resolver->setDefaults(
    //         [
    //             'data_class' => Product::class,
    //         ]
    //     );
    // }
}
