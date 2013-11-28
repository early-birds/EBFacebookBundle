<?php

namespace EB\FacebookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use EB\FacebookBundle\Entity\Invitation;

class FacebookController extends Controller
{
    public function isDev()
    {
        $env = $this->container->getParameter('kernel.environment');
        return $env === 'dev' ? true : false;
    }
    
    public function getParam($param)
    {
        $ebParam = $this->container->getParameter('eb_facebook.' . $param);
        return $ebParam;
    }
    
    public function getTemplate($template)
    {
        $templates = $this->getParam('templates');
        return $templates[$template];
    }
    
    public function getBaseTemplate()
    {
        return $this->getTemplate('layout');
    }
    
    public function facebookReturn($viewName, $data = array())
    {
        $data['base_template'] = $this->getBaseTemplate();
        $view = $this->getTemplate($viewName);
        
        return $this->render($view, $data);
    }
    
    public function homeAction()
    {
        $GET = $this->getRequest()->query;
        $fixcookieUrl = $this->getParam('fixcookie');
        if ($fixcookieUrl && preg_match('/Safari/i',$_SERVER['HTTP_USER_AGENT']) && count($_COOKIE) === 0) {
            die('<script>top.location = "'.$fixcookieUrl.'"</script>');
        }
        if ($this->getParam('skip_app')) {
            if(!is_null($GET->get('request_ids')) || !is_null($GET->get('fb_source'))) die('<script>top.location = "'.$this->getParam('tab_url').'"</script>');
        }

        if ($this->isDev()) $this->getRequest()->getSession()->set('liked', true);
        
        return $this->facebookReturn('home');
    }
    
    public function registerAction()
    {
        $user    = $this->getUser();
        if (!$this->isDev() && $user->getValidated()) return $this->redirect($this->generateUrl('game'));
        
        $request      = $this->getRequest();
        $userType     = $this->getParam('form_class');
        $userClass    = $this->getParam('user_class');
        $translation  = $this->getParam('translation');
        
        $form = $this->createForm(new $userType($userClass, $translation, $this->get('translator')), $user);
        if ($request->getMethod() == 'POST') {
            $m = $this->getDoctrine()->getManager();
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user->setValidated(true);
                $m->persist($user);
                $m->flush();
                
                return $this->redirect($this->generateUrl('game'));
            }
        }
        
        return $this->facebookReturn('register', array(
            'form'  => $form->createView()
        ));
    }

    public function countAction()
    {
        $m          = $this->getDoctrine()->getManager();
        $request    = $this->getRequest();
        $user       = $this->getUser();
        $friends    = $request->request->get('to', false);
        $count      = $user->getCount();

        if($friends){
            $r = $m->getRepository('EBFacebookBundle:Invitation');
            $friendsAlreadyInvited = $r->createQueryBuilder('fi')
                ->select('fi.friendId')
                ->where('fi.sponsor = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getArrayResult();

            $ids = array();
            foreach($friendsAlreadyInvited as $f){
                $ids[] = $f['friendId'];
            }

            foreach($friends as $friend){
                if(!in_array($friend,$ids)){
                    $f  = new Invitation();
                    $f->setSponsor($user);
                    $f->setFriendId($friend);
                    $m->persist($f);
                    $count++;
                }
            }
            
            if ($count !== $user->getCount()){
                $user->setCount($count);
                $m->persist($user);
            }
                
            $m->flush();
        }
      
        $response = new Response(json_encode($count));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
