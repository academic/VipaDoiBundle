<?php

namespace BulutYazilim\OjsDoiBundle\Form\Type;

use BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig;
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
            ->add('prefix', 'text', array('attr' => array('help_text' => 'doi.prefix.helpText')))
            ->add('postfix', 'text', array('required' => false, 'attr' => array('help_text' => 'doi.postfix.helpText', 'placeholder' => (new CrossrefConfig())->getPostfix())));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'BulutYazilim\OjsDoiBundle\Entity\CrossrefConfig'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bulut_yazilim_doi_bundle_config';
    }
}
