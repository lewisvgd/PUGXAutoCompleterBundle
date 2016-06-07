<?php

namespace PUGX\AutocompleterBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use PUGX\AutocompleterBundle\Form\Transformer\ObjectToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AutocompleteType extends AbstractType
{
    /**
     * @var ManagerRegistry
     */
    private $registry;
    private $container;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry, $container)
    {
        $this->registry  = $registry;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['class']) && empty($options['entity_alias'])) {
            throw new InvalidConfigurationException('Option "class" or "entity_alias" must be set.');
        }

        if ($options['entity_alias']) {

            $entities = $this->container->getParameter('shtumi.autocomplete_entities');

            if (!isset($entities[$options['entity_alias']])) {

                throw new InvalidConfigurationException('Invalid value for "entity_alias"');
            }

            $class = $entities[$options['entity_alias']]['class'];

            $builder->setAttribute('entity_alias', $options['entity_alias']);
        }

        if ($options['class']) {
            $class = $options['class'];
        }

        $transformer = new ObjectToIdTransformer($this->registry, $class);
        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class'             => '',
            'entity_alias'      => '',
            'invalid_message'   => 'The selected item does not exist',
        ));
    }

    /**
     * BC for Symfony < 2.7.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        // BC for Symfony < 3
        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            return 'text';
        }

        return 'Symfony\Component\Form\Extension\Core\Type\TextType';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return $this->getName();
    }

    /**
     * BC for Symfony < 3.0.
     */
    public function getName()
    {
        return 'autocomplete';
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['entity_alias'] = $form->getConfig()->getAttribute('entity_alias');
    }
}
