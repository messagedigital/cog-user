<?php

namespace Message\User\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SimpleSearch extends AbstractType
{
public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('term', 'text', [
            'attr' => [
                'placeholder' => 'Name/Email/Town/Postcode/Telephone',
            ]
        ]);
    }

    public function getName()
    {
        return 'user_simple_search';
    }

}