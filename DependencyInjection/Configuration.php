<?php

namespace PUGX\AutocompleterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('pugx_autocompleter');

        $root
            ->children()
                ->arrayNode('autocomplete_entities')
                ->useAttributeAsKey('id')
                ->prototype('array')
                ->children()
                    ->scalarNode('class')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('property')
                        ->defaultValue('title')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('role')
                        ->defaultValue('IS_AUTHENTICATED_ANONYMOUSLY')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('search')
                        ->defaultValue('begins_with')
                        ->cannotBeEmpty()
                    ->end()
                    ->booleanNode('case_insensitive')
                        ->defaultTrue()
                    ->end()
                    ->scalarNode('custom_search')
                    ->end()
                    ->scalarNode('custom_get')
                    ->end()
                ->end()
            ->end();


        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        return $treeBuilder;
    }
}
