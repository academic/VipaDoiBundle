<?php

namespace OkulBilisim\OjsDoiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrossrefConfigType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('password')
            ->add('fullName')
            ->add('email', 'email')
            ->add('prefix', 'text', array('attr' => array('help_text' => 'doi.prefix.helpText')));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OkulBilisim\OjsDoiBundle\Entity\CrossrefConfig'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'okul_bilisim_doi_bundle_config';
    }
}
