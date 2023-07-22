<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $trick = $options['data'] ?? null;
        $builder
            ->add('name', TextType::class,[
                'label'=> "Name",
                'attr' => [
                    'class'=>'form-control'
                ]
            ])
            ->add('description', TextareaType::class,[
                'label'=> "Description",
                'attr' => [
                    'class'=>'form-control'
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name', 
                'data' => $trick ? $trick->getCategory() : null, 
                'attr' => [
                    'class'=>'form-control'
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
