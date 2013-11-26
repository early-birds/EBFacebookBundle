<?php

namespace EB\FacebookBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EBFacebookExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        
        $mandatoryPermissions = array('user_birthday', 'user_likes', 'user_friends', 'user_interests', 'friends_birthday', 'friends_likes');
        $recommendedPermissions = array('user_about_me', 'user_activities', 'user_relationships', 'friends_hometown', 'friends_location', 'friends_interests', 'friends_relationships');
        $allPermissions = array_merge($mandatoryPermissions, $recommendedPermissions);
        if (!$config['permissions']) $config['permissions'] = $allPermissions;

        foreach (array('app_id', 'secret', 'tab_url', 'culture', 'translation', 'permissions', 'templates', 'fixcookie', 'user_class', 'form_class') as $attribute) {
            $container->setParameter('eb_facebook.'.$attribute, $config[$attribute]);
        }
    }
    
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config_eb_facebook = $this->processConfiguration(new Configuration(), $configs);
        
        $container->prependExtensionConfig('fos_facebook', array(
            'alias' => 'facebook',
            'app_id' => $config_eb_facebook['app_id'],
            'secret' => $config_eb_facebook['secret'],
            'permissions' => $config_eb_facebook['permissions']
        ));      
        
        $config_fos_user = array(
            'db_driver'     => 'orm',
            'firewall_name' => 'main',
            'user_class'    => $config_eb_facebook['user_class']
        );
        
        $container->prependExtensionConfig('fos_user', $config_fos_user);
    }
}
