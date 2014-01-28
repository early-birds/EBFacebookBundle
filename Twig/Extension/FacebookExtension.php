<?php

namespace EB\FacebookBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FacebookExtension extends \Twig_Extension
{
    protected $container;
 
    /**
    * Constructor.
    *
    * @param ContainerInterface $container
    */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function getFunctions()
    {
        return array(
            'ebfacebook_init_permissions' => new \Twig_Function_Method($this, 'initPermissions', array('is_safe' => array('html'))),
            'facebook_custom_login_button' => new \Twig_Function_Method($this, 'renderCustomLoginButton', array('is_safe' => array('html')))
        );
    }

    public function initPermissions() {
        $permissions = implode(',', $this->container->getParameter('eb_facebook.permissions'));

        return ("<script type=\"text/javascript\">$(document).ready(function () { ebFacebook.setPermissions('".$permissions."'); }); </script>");
    }

    public function renderCustomLoginButton($params = array()) {
        $value = isset($params['value']) ? $params['value'] : 'Participer';
        $class = isset($params['class']) ? $params['class'] : 'btnFbLogin';

        return ("<input type='button' value='".$value."' class='".$class."' onclick='ebFacebook.ebLogin();return false;' />");
    }

    public function getName()
    {
        return 'twig_extension_eb_facebook';
    }
}