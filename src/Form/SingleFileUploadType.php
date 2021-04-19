<?php

declare(strict_types=1);


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class SingleFileUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class,[
                'constraints' => [
                    new File([
                        'mimeTypesMessage' => 'Test',
                        'mimeTypes' => [
                            'image/*',
                            'application/pdf'
                        ]
                    ])
                ]
            ])
            ->add('docType', TextType::class)
        ;
    }
    
//    public function getBlockPrefix()
//    {
//        return '';
//    }
}
