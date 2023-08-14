<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\Category;
use ImageType;
use VideoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $trick = $options['data'] ?? null;
        $builder
            ->add('name', TextType::class, [
                'label' => "Name",
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'data' => $trick ? $trick->getCategory() : null,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])

            ->add('mainImageFile', FileType::class, [
                'label' => false,
                'required' => false, 
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'Please upload an image under 5MB',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF)',
                    ]),
                ],
                'attr' => [
                    'accept' => 'image/*', 
                    'class' => 'form-control'
                ],
            ])

            ->add('multiple_images', FileType::class, [
                'label' => false,
                'required' => false, 
                'mapped' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '5M',
                            'maxSizeMessage' => 'Please upload an image under 5MB',
                            'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                            'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF)',
                        ]),
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*', 
                    'class' => 'form-control'
                ],
            ])

            ->add('video_url', UrlType::class, [
                'label' => false,
                'required' => false, 
                'mapped' => false,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Your comment must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your comment cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "Youtube video URL"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
