<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AddLocationHeaderSubscriber implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * AddLocationHeaderSubscriber constructor.
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['addLocationHeader'],
        ];
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function addLocationHeader(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if (($data = $request->attributes->get('data')) && $data instanceof User && Request::METHOD_POST === $request->getMethod()) {
            $event->getResponse()->headers->set('Location', sprintf(
                '%s',
                $this->urlGenerator->generate('api_users_get_item', ['id' => $data->getId()], UrlGeneratorInterface::ABS_URL))
            );
        }
    }
}
