<?php

namespace EB\FacebookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class UserType extends AbstractType
{
    private $class;
    private $translation;
    private $translator;

    /**
    * @param string $class The User class name
    */
    public function __construct($class, $translation, $translator)
    {
        $this->class = $class;
        $this->translation = $translation;
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', null, array('required' => true, 'label' => 'form.label.firstname', 'attr' => array('class' => 'required')))
            ->add('lastname', null, array('required' => true, 'label' => 'form.label.lastname', 'attr' => array('class' => 'required')))
            ->add('birthday', 'birthday', array(
                'required' => true,
                'widget' => 'text',
                'format' => $this->translator->trans('form.format.birthday', array(), $this->translation),
                'label' => 'form.label.birthday')
            )
            ->add('email', 'email', array(
                'required' => true,
                'label' => 'form.label.email',
                'constraints' => array(
                    new NotBlank(array('message' => 'eb_facebook.required')),
                    new Email(array('message' => 'eb_facebook.invalid'))
                ),
                'attr' => array('class' => 'required')
            ))
            ->add('address', null, array('required' => true, 'label' => 'form.label.address', 'attr' => array('class' => 'required')))
            ->add('zipcode_fr', null, array('required' => true, 'label' => 'form.label.zipcode', 'attr' => array('class' => 'required')))
            ->add('city', null, array('required' => true,'label' => 'form.label.city','attr' => array('class' => 'required')))
            ->add('phone', null, array('required' => false,'label' => 'form.label.phone'))
            ->add('readConditions', 'checkbox', array(
                'label' => 'form.label.readConditions',
                'mapped' => false,
                'required' => false,
                'constraints' => array(
                    new True(array('message' => 'form.error.readConditions'))
                ),
                'attr' => array('class' => 'required')
            ))
            ->add('offersEmail', 'checkbox', array('label' => 'form.label.offers.email', 'required' => false))
            ->add('offersSms', 'checkbox', array('label' => 'form.label.offers.sms', 'required' => false))
        ;
        
        $builder->setRequired(false);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'            => $this->class,
            'translation_domain'    => $this->translation,
            'csrf_protection'       => false
        ));
    }

    public function getName()
    {
        return 'eb_facebookbundle_usertype';
    }
}