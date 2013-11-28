<?php

namespace EB\FacebookBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('eb_facebook')
            ->children()
                ->scalarNode('app_id')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('tab_url')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('skip_app')->defaultValue(false)->end()
                ->scalarNode('culture')->defaultValue('fr_FR')->end()
                ->scalarNode('fixcookie')->defaultValue(false)->end()
                ->scalarNode('user_class')->defaultValue('EB\FacebookBundle\Entity\User')->end()
                ->scalarNode('form_class')->defaultValue('EB\FacebookBundle\Form\UserType')->end()
                ->scalarNode('translation')->defaultValue('EBFacebookBundle')->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('layout')->defaultValue('EBFacebookBundle::layout.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('home')->defaultValue('EBFacebookBundle::home.html.twig')->cannotBeEmpty()->end()
                        ->scalarNode('register')->defaultValue('EBFacebookBundle::register.html.twig')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('permissions')->prototype('scalar')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
