<?php

namespace EB\FacebookBundle\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function __construct(BaseFacebook $facebook, $userManager, $validator, $extendedAccessToken)
    {
        $this->facebook = $facebook;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->extendedAccessToken = $extendedAccessToken;
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
                $this->facebook->setExtendedAccessToken();
                $user->setExtendedAccessToken($this->facebook->getAccessToken());
                $user->setExpirationExtendedAccessToken(new \DateTime('+2 month'));
            }
            $this->userManager->updateUser($user);
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on facebook');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getFacebookId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getFacebookId());
    }

    public function refreshExtendAccessToken()
    {
        if ($this->extendedAccessToken) {
            $this->facebook->setExtendedAccessToken();
            var_dump($this->facebook->getAccessToken());
            exit;
        }
    }
}