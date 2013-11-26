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
            'facebook_login_function' => new \Twig_Function_Method($this, 'renderLoginFunction', array('is_safe' => array('html'))),
            'facebook_custom_login_button' => new \Twig_Function_Method($this, 'renderCustomLoginButton', array('is_safe' => array('html')))
        );
    }
    
    public function renderLoginFunction() {
        $permissions = implode(',', $this->container->getParameter('fos_facebook.permissions')); 
                
        return ("<script type=\"text/javascript\">function fbLogin() { FB.login(function(response) { }, {scope: '".$permissions."'}); } </script>");
    }
    
    public function renderCustomLoginButton($params = array()) {   
        $value = isset($params['value']) ? $params['value'] : 'Participer';
        $class = isset($params['class']) ? $params['class'] : 'btnFbLogin';
        
        return ("<input type='button' value='".$value."' class='".$class."' onclick='fbLogin();return false;' />");
    }
    
    public function getName()
    {
        return 'twig_extension_eb_facebook';
    }
}