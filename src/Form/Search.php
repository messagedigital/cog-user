<?php

namespace Message\User\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Search extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('forename', 'text', [
            'attr' => [
                'placeholder' => 'Forename',
            ]
        ]);

        $builder->add('surname', 'text', [
            'attr' => [
                'placeholder' => 'Surname',
            ]
        ]);

        $builder->add('email', 'text', [
            'attr' => [
                'placeholder' => 'E-Mail',
            ]
        ]);

        $builder->add('telephone', 'text', [
            'attr' => [
                'placeholder' => 'Telephone',
            ]
        ]);

        $builder->add('town', 'text', [
            'attr' => [
                'placeholder' => 'Town',
            ]
        ]);

        $builder->add('postcode', 'text', [
            'attr' => [
                'placeholder' => 'Postcode',
            ]
        ]);

    }

    public function getName()
    {
        return 'user_search';
    }

}