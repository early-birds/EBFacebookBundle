<?php

namespace EB\FacebookBundle\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use \BaseFacebook;
use \FacebookApiException;

class FacebookProvider implements UserProviderInterface
{
    /**
     * @var \Facebook
     */
    protected $facebook;
    protected $userManager;
    protected $validator;
    protected $extendedAccessToken;
    protected $request;

    public function __construct(BaseFacebook $facebook, $userManager, $validator, $extendedAccessToken, RequestStack $requestStack)
    {
        $this->facebook = $facebook;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->extendedAccessToken = $extendedAccessToken;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getFacebook() {
        return $this->facebook;
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    public function findUserByFbId($fbId)
    {
        return $this->userManager->findUserBy(array('facebookId' => $fbId));
    }

    public function findUserByEmail($email)
    {
        return $this->userManager->findUserBy(array('email' => $email));
    }

    public function loadUserByUsername($username)
    {
        $user = $this->findUserByFbId($username);

        try {
            $fbdata = $this->facebook->api('/me');
        } catch (FacebookApiException $e) {
            throw new UsernameNotFoundException('The user is not authenticated on facebook');
            $fbdata = null;
        }

        if(!isset($fbdata['email'])) $fbdata['email'] = $fbdata['username']."@facebook.com";

        if (!empty($fbdata)) {
            if (empty($user)) {
                $user = $this->findUserByEmail($fbdata['email']);
                if(empty($user)){
                    $user = $this->userManager->createUser();
                    $user->setEnabled(true);
                    $user->setPassword('');
                }
            }

            $user->setFBData($fbdata);

            if (count($this->validator->validate($user, 'Facebook'))) {
                throw new UsernameNotFoundException('The facebook user could not be stored');
            }

            if ($this->extendedAccessToken) {
                $extendedAccessToken = $this->getExtendedAccessToken();
                $this->facebook->setAccessToken($extendedAccessToken['access_token']);
                $user->setExtendedAccessToken($extendedAccessToken['access_token']);
                $user->setExpirationExtendedAccessToken($extendedAccessToken['expires']);
            }

            $user->setIp($this->request->getClientIp());
            $this->userManager->updateUser($user);
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on facebook');
        }

        return $user;
    }

    public function getExtendedAccessToken () {
        $params = array(
            'client_id' => $this->facebook->getAppId(),
            'client_secret' => $this->facebook->getAppSecret(),
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $this->facebook->getAccessToken(),
        );

        $url = 'https://graph.facebook.com/oauth/access_token?';
        foreach ($params as $key => $param) $url .= $key . '=' . $param. '&';
        $access_token_response = file_get_contents($url);

        $response_params = array();
        parse_str($access_token_response, $response_params);

        $expiresDate = new \DateTime();
        $expiresDate->setTimestamp($response_params['expires'] + time());

        return array(
            'access_token' => $response_params['access_token'],
            'expires' => $expiresDate,
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getFacebookId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getFacebookId());
    }
}