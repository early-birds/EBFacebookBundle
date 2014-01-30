<?php

namespace EB\FacebookBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class DoctrineListener implements EventSubscriber
{
    protected $userClass;
    protected $extendedAccessToken;

    public function __construct($userClass, $extendedAccessToken)
    {
        $this->userClass = $userClass;
        $this->extendedAccessToken = $extendedAccessToken;
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

                if ($this->extendedAccessToken) {
                    $metadata->mapField(array(
                        'fieldName' => 'extendAccessToken',
                        'type' => 'text'
                    ));
                    $metadata->mapField(array(
                        'fieldName' => 'expirationExtendAccessToken',
                        'type' => 'datetime'
                    ));
                }
            }
        }
    }
}