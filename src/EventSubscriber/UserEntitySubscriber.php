<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEntitySubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * UserEntitySubscriber constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setCurrentClient', EventPriorities::PRE_WRITE],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function setCurrentClient(GetResponseForControllerResultEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        /** @var UserInterface $client */
        $client = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof User || Request::METHOD_POST !== $method) {
            return;
        }

        $user->setClient($client);
    }
}
