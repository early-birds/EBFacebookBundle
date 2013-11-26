<?php

namespace EB\FacebookBundle\Listener;
 
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \BaseFacebook;

class PreControllerListener
{
    protected $session;
    protected $router;
    protected $facebookApi;
   
    public function __construct(Session $session, RouterInterface $router, BaseFacebook $facebookApi)
    {
        $this->session = $session;
        $this->router = $router;
        $this->facebookApi = $facebookApi;
    }
 
    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST == $event->getRequestType()) {
            $request = $event->getRequest();
            $route   = $request->attributes->get('_route');
            $fbToken = $request->get('signed_request', false);
            $liked   = $this->session->get('liked', false);

            if($fbToken){
                $signedRequest = $this->facebookApi->getSignedRequest();
                if(!is_null($signedRequest['page']['liked'])){
                    $liked = $signedRequest['page']['liked'];
                    $this->session->set('liked', $liked);
                }
            }

            if (!$liked) {
                $this->session->set('liked', false);
                if ($route === 'eb_facebook_home') return;
                
                $redirectUrl = $this->router->generate('eb_facebook_home');
                $event->setController(function() use ($redirectUrl) {
                    return new RedirectResponse($redirectUrl);
                });
            } 
        }
               
    }
}