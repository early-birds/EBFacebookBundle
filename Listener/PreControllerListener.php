<?php

namespace EB\FacebookBundle\Listener;
 
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \BaseFacebook;
use \EB\FacebookBundle\Provider\FacebookProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PreControllerListener
{
    protected $router;
    protected $facebookApi;
    protected $facebookProvider;
    protected $securityContext;
    protected $config;

    public function __construct(RouterInterface $router, BaseFacebook $facebookApi, FacebookProvider $facebookProvider, $securityContext, $config)
    {
        $this->router = $router;
        $this->facebookApi = $facebookApi;
        $this->facebookProvider = $facebookProvider;
        $this->securityContext = $securityContext;
        $this->config = $config;
    }
 
    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST == $event->getRequestType()) {
            $request  = $event->getRequest();
            $session  = $request->getSession();
            $route    = $request->attributes->get('_route');
            $GET      = $request->query;
            $url      = $this->router->generate($route, $request->attributes->get('_route_params'));
            $appParam = false;
        
            //Dont use pre controller for some links
            if (in_array($route, $this->config['precontroller_exclude_route'])) return;
            if (preg_match('#'.implode('|', $this->config['precontroller_exclude_route_start']).'#', $route)) return;
            if (preg_match('#'.implode('|', $this->config['precontroller_exclude_pattern']).'#', $url)) return;

            $fbToken = $request->get('signed_request', false);
            $signedRequest = $fbToken ? $this->facebookApi->getSignedRequest() : false;

            /* Auto connect */
            if ($signedRequest && !$this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
                $uId = $this->facebookApi->getUser();
                try {
                    $user = $this->facebookProvider->loadUserByUsername($uId);
                    if($user){
                        $token = new UsernamePasswordToken($user, null, $this->config['firewall'], $user->getRoles());
                        $this->securityContext->setToken($token);
                    }
                } catch (\Exception $e) {
                    //Application not accepted
                }
            }

            foreach ($this->config['app_params'] as $p) {
                if (!is_null($GET->get($p))) {
                    $appParam = true;
                    break;
                }
            }
            
            if (!is_null($GET->get('request_ids')) || !is_null($GET->get('fb_source')) || $appParam) {
                if ($appParam) {
                    foreach ($this->config['app_params'] as $appParam) {
                        if (!is_null($GET->get($appParam))) $session->set('app_params.'.$appParam, $GET->get($appParam));
                    }
                }
                if ($this->config['skip_app'] && $this->config['tab_url']) {
                    die('<script>top.location = "'.$this->config['tab_url'].'"</script>');
                }
            } else if ($this->config['fixcookie'] && preg_match('/Safari/i',$_SERVER['HTTP_USER_AGENT']) && !preg_match('/Chrome/i', $_SERVER['HTTP_USER_AGENT']) && count($_COOKIE) === 0) {
                die('<script>top.location = "'.$this->config['fixcookie'].'"</script>');
            }

            /* Like security */
            if ($this->config['tab_like'] && $this->config['tab_url']) {
                $liked   = $session->get('liked', false);

                if($fbToken){
                    if(!is_null($signedRequest['page']['liked'])){
                        $liked = $signedRequest['page']['liked'];
                        $session->set('liked', $liked);
                    }
                }

                if (!$liked) {
                    $session->set('liked', false);
                    if ($route === $this->config['homepage']) return;
                    $session->set('referer_url', $url);

                    $redirectUrl = $this->router->generate($this->config['homepage']);
                    $event->setController(function() use ($redirectUrl) {
                        return new RedirectResponse($redirectUrl);
                    });
                } else {
                    $refererUrl = $session->get('referer_url', false);
                    if ($refererUrl) {
                        $session->remove('referer_url');
                        $event->setController(function() use ($refererUrl) {
                            return new RedirectResponse($refererUrl);
                        });
                    }
                }
            } else $session->set('liked', true);
        }
    }
}