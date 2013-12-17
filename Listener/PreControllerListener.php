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
    protected $tabLike;
    protected $tabLikeExcludeRoute;
    protected $tabLikeExcludeRouteStart;
    protected $tabLikeExcludePattern;
   
    public function __construct(Session $session, RouterInterface $router, BaseFacebook $facebookApi, $tabLike, $tabLikeExcludeRoute, $tabLikeExcludeRouteStart, $tabLikeExcludePattern)
    {
        $this->session = $session;
        $this->router = $router;
        $this->facebookApi = $facebookApi;
        $this->tabLike = $tabLike;    
        $this->tabLikeExcludeRoute = $tabLikeExcludeRoute;    
        $this->tabLikeExcludeRouteStart = $tabLikeExcludeRouteStart;    
        $this->tabLikeExcludePattern = $tabLikeExcludePattern;    
    }
 
    public function onKernelController(FilterControllerEvent $event)
    {
        if ($this->tabLike && HttpKernel::MASTER_REQUEST == $event->getRequestType()) {
            $request = $event->getRequest();
            $route   = $request->attributes->get('_route');
                        
            if (in_array($route, $this->tabLikeExcludeRoute)) return;
            if (preg_match('#'.implode('|', $this->tabLikeExcludeRouteStart).'#', $route)) return;
            $url = $this->router->generate($route, $request->attributes->get('_route_params'));
            if (preg_match('#'.implode('|', $this->tabLikeExcludePattern).'#', $url)) return;

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