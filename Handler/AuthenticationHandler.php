<?php

namespace EB\FacebookBundle\Handler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthenticationHandler implements AuthenticationSuccessHandlerInterface {
    
    private $defaultTargetRoute;
    private $router;

    public function __construct(UrlGeneratorInterface $router, $defaultTargetRoute)
    {
        $this->router = $router;
        $this->defaultTargetRoute = $defaultTargetRoute;
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $session = $request->getSession();
        $target_url = $session->get('target_url', false);
        
        if ($target_url) {
            $session->remove('target_url');
            $url = $target_url;
        } else $url = $this->router->generate($this->defaultTargetRoute);

        return new RedirectResponse($url);
    }
    
}