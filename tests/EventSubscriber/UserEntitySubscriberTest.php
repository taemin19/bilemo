<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Client;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserEntitySubscriberTest extends TestCase
{
    /**
     * This test checks that the method getSubscribedEvents()
     * was correctly returned (a constant data)
     */
    public function testConfiguration()
    {
        $result = UserEntitySubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::VIEW, $result);
        $this->assertEquals(
            ['setCurrentClient', EventPriorities::PRE_WRITE],
            $result[KernelEvents::VIEW]
        );
    }

    /**
     * This test checks that the method setCurrentClient()
     * was correctly called (set the client of user)
     *
     * @param string $className
     * @param bool $shouldCallSetClient
     * @param string $method
     *
     * @dataProvider providerSetClientCall
     */
    public function testSetClientCall(string $className, bool $shouldCallSetClient, string $method)
    {
        $entityMock = $this->getEntityMock($className, $shouldCallSetClient);
        $tokenStorageMock = $this->getTokenStorageMock();
        $eventMock = $this->getEventMock($method, $entityMock);

        (new UserEntitySubscriber($tokenStorageMock))->setCurrentClient(
            $eventMock)
        ;
    }

    /**
     * @return array
     */
    public function providerSetClientCall(): array
    {
        return [
            [User::class, true, 'POST'],
            [User::class, false, 'GET'],
            ['NonExisting', false, 'POST'],
            ['NonExisting', false, 'GET']
        ];
    }

    /**
     * This helper method mocks the Token Storage
     * and checks that the current client is correctly fetched
     *
     * @return MockObject|TokenStorageInterface
     */
    private function getTokenStorageMock(): MockObject
    {
        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->getMockForAbstractClass();
        $tokenMock->expects($this->once())
            ->method('getUser')
            ->willReturn(new Client());

        $tokenStorageMock = $this->getMockBuilder(TokenStorageInterface::class)
            ->getMockForAbstractClass();
        $tokenStorageMock->expects($this->once())
            ->method('getToken')
            ->willReturn($tokenMock);

        return $tokenStorageMock;
    }

    /**
     * This helper method mocks the Controller Result Event
     * and checks that the methods getControllerResult() and getMethod()
     * are correctly called on the event
     *
     * @param string $method
     * @param $controllerResult
     * @return MockObject|GetResponseForControllerResultEvent
     */
    private function getEventMock(string $method, $controllerResult): MockObject
    {
        $requestMock = $this->getMockBuilder(Request::class)
            ->getMock();
        $requestMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        $eventMock = $this->getMockBuilder(GetResponseForControllerResultEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($controllerResult);
        $eventMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);

        return $eventMock;
    }

    /**
     * This helper method mocks an entity
     * and checks that the method setClient() is correctly called or not called
     *
     * @param string $className
     * @param bool $shouldCallSetClient
     * @return MockObject
     */
    private function getEntityMock(string $className, bool $shouldCallSetClient): MockObject
    {
        $entityMock = $this->getMockBuilder($className)
            ->setMethods(['setClient'])
            ->getMock();
        $entityMock->expects($shouldCallSetClient ? $this->once() : $this->never())
            ->method('setClient');

        return $entityMock;
    }
}
