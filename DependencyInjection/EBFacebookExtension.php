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
    private $mandatoryPermissions = array('email', 'user_birthday', 'user_likes', 'user_friends', 'user_interests', 'friends_birthday', 'friends_likes');
    private $recommendedPermissions = array('user_about_me', 'user_activities', 'user_relationships', 'friends_hometown', 'friends_location', 'friends_interests', 'friends_relationships');
    private $patterns = array('admin', 'login', 'logout', 'login_facebook_check');
    private $routesStart = array('_imagine', '_sonata', '_security', '_profiler', '_assetic', '_configurator');
            
    public function getDefaultPermissions() {
        return array_merge($this->mandatoryPermissions, $this->recommendedPermissions);
    }
    
    public function getDefaultPatterns() {
        $excludePatterns = array();
        foreach ($this->patterns as $pattern) $excludePatterns[] = "\/".$pattern."((\/\w+)+|\/?)$";
        
        return $excludePatterns;
    }
    
    public function getDefaultRoutesStart() {
        $excludeRoutesStart = array();
        foreach ($this->routesStart as $route) $excludeRoutesStart[] = "^".$route;
        
        return $excludeRoutesStart;
    }
    
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (!$config['permissions']) $config['permissions'] = $this->getDefaultPermissions();
        if (!$config['precontroller_exclude_route_start']) $config['precontroller_exclude_route_start'] = $this->getDefaultRoutesStart();
        if (!$config['precontroller_exclude_pattern']) $config['precontroller_exclude_pattern'] = $this->getDefaultPatterns();
        
        if ($config['add_permissions']) $config['permissions'] = array_merge($config['permissions'], $config['add_permissions']);
        if ($config['less_permissions']) $config['permissions'] = array_diff($config['permissions'], $config['less_permissions']);

        $container->setParameter('eb_facebook.config', $config);
        foreach (array(
                    'app_id', 'secret', 'default_target_route', 'firewall', 'register', 'extended_access_token', 'homepage',
                    'tab_url', 'app_params', 'tab_like', 'precontroller_exclude_route', 'precontroller_exclude_route_start',
                    'precontroller_exclude_pattern', 'skip_app', 'culture', 'translation', 'permissions',
                    'add_permissions', 'less_permissions', 'templates', 'fixcookie', 'user_class', 'form_class'
                 ) as $attribute) {
            $container->setParameter('eb_facebook.'.$attribute, $config[$attribute]);
        }
    }
    
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config_eb_facebook = $this->processConfiguration(new Configuration(), $configs);
        if (!$config_eb_facebook['permissions']) $config_eb_facebook['permissions'] = $this->getDefaultPermissions();
        
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