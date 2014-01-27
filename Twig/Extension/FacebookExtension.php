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
        );
    }

    public function initPermissions() {
        $permissions = implode(',', $this->container->getParameter('eb_facebook.permissions'));

        return ("<script type=\"text/javascript\">ebFacebook.setPermissions('".$permissions."');</script>");
    }

    public function getName()
    {
        return 'twig_extension_eb_facebook';
    }
}