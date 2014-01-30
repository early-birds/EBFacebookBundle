<?php

namespace EB\FacebookBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class DoctrineListener implements EventSubscriber
{
    protected $userClass;
    protected $extendededAccessToken;

    public function __construct($userClass, $extendededAccessToken)
    {
        $this->userClass = $userClass;
        $this->extendededAccessToken = $extendededAccessToken;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        if (!in_array($metadata->getName(),['EB\FacebookBundle\Entity\Invitation',$this->userClass])) {
            return;
        } else {
            if ($metadata->getName() === 'EB\FacebookBundle\Entity\Invitation') {
                $metadata->mapManyToOne(array(
                    'targetEntity'  => $this->userClass,
                    'fieldName'     => 'sponsor',
                    'inversedBy'    => 'invitation'

                ));
            } elseif ($metadata->getName() === $this->userClass) {
                $metadata->mapOneToMany(array(
                    'targetEntity'  => 'EB\FacebookBundle\Entity\Invitation',
                    'fieldName'     => 'invitation',
                    'mappedBy'      => 'sponsor'
                ));

                if ($this->extendededAccessToken) {
                    $metadata->mapField(array(
                        'fieldName' => 'extendedAccessToken',
                        'type' => 'text'
                    ));
                    $metadata->mapField(array(
                        'fieldName' => 'expirationExtendedAccessToken',
                        'type' => 'datetime'
                    ));
                }
            }
        }
    }
}