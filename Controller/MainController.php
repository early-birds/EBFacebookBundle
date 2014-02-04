<?php

namespace EB\FacebookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use EB\FacebookBundle\Entity\Invitation;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
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
    
    public function homeAction(Request $request)
    {
        if ($this->isDev()) $request->getSession()->set('liked', true);
        
        return $this->facebookReturn('home');
    }
    
    public function registerAction(Request $request)
    {
        $user    = $this->getUser();
        $registerCallback = $this->container->getParameter('eb_facebook.register_callback');
        if ($registerCallback && $user->getValidated()) return $this->redirect($this->generateUrl($registerCallback));
        
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

    public function countAction(Request $request)
    {
        $m          = $this->getDoctrine()->getManager();
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

    public function setRouteSessionAction(Request $request)
    {
        $direct_url = $request->request->get('direct_url', false);
        $route = $request->request->get('route', false);
        $route_params = $request->request->get('route_params', false);
        $data = array('res' => 1);

        try {
            $data['url'] = $direct_url ? $direct_url : $this->generateUrl($route, $route_params);
            $request->getSession()->set('target_url', $data['url']);
        } catch (\Exception $e) {
            $data['res'] = 0;
        }

        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
