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
            'ebfacebook_initialize' => new \Twig_Function_Method($this, 'initialize', array('is_safe' => array('html'))),
            'facebook_custom_login_button' => new \Twig_Function_Method($this, 'renderCustomLoginButton', array('is_safe' => array('html')))
        );
    }

    public function initialize($params = array()) {

        $arrayUser = null;
        if (isset($params['user']) && $params['user']) {
            $user = $params['user'];
            $arrayUser = array(
                "fullname"      => $user->getFirstname() . " " .$user->getLastname(),
                "firstname"     => $user->getFirstname(),
                "lastname"      => $user->getLastname(),
                "facebookId"    => $user->getFacebookId()
            );
        }

        $permissions = implode(',', $this->container->getParameter('eb_facebook.permissions'));

        $script  = "<script type=\"text/javascript\">";
        $script .= "$(document).ready(function () {";
        $script .= "if (typeof EFB !== 'undefined' && EFB !== null) {";

        if ($permissions) $script .= "EFB.setPermissions('".$permissions."');";
        if ($arrayUser) $script .= "EFB.setMe('".json_encode($arrayUser)."');";

        $script .= "}"; //end if
        $script .= "});"; //end document.ready
        $script .= "</script>"; //end script

        return $script;
    }

    public function renderCustomLoginButton($params = array()) {
        $value = isset($params['value']) ? $params['value'] : 'Participer';
        $class = isset($params['class']) ? $params['class'] : 'btnFbLogin';

        return ("<input type='button' value='".$value."' class='fbLogin ".$class."' />");
    }

    public function getName()
    {
        return 'twig_extension_eb_facebook';
    }
}