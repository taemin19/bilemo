<?php

namespace App\Tests\Command;

use App\Command\CreateClientCommand;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateClientCommandTest extends KernelTestCase
{
    private $clientData = [
        'name' => 'Doe',
        'username' => 'john',
        'password' => 'password',
    ];

    /**
     * This test provides all the arguments required by the command, so the
     * command runs non-interactively and it won't ask for any argument.
     */
    public function testCreateClientNonInteractive()
    {
        $input = $this->clientData;

        $this->executeCommand($input);
        $this->assertClientCreated();
    }

    /**
     * This test doesn't provide all the arguments required by the command, so
     * the command runs interactively and it will ask for the value of the missing
     * arguments.
     */
    public function testCreateClientInteractive()
    {
        $this->executeCommand(
        // these are the missing arguments
            [],
            // these are the responses given to the questions asked by the command
            // to get the value of the missing required arguments
            array_values($this->clientData)
        );
        $this->assertClientCreated();
    }

    /**
     * This helper method checks that the client was correctly created and saved
     * in the database.
     */
    private function assertClientCreated()
    {
        $container = self::$kernel->getContainer();
        /** @var Client $client */
        $client = $container->get('doctrine')->getRepository(Client::class)->findOneBy(['username' => $this->clientData['username']]);
        $this->assertNotNull($client);
        $this->assertSame($this->clientData['name'], $client->getName());
        $this->assertSame($this->clientData['username'], $client->getUsername());
        $this->assertTrue($container->get('security.password_encoder')->isPasswordValid($client, $this->clientData['password']));
    }
    /**
     * This helper method abstracts the boilerplate code needed to test the
     * execution of a command.
     *
     * @param array $arguments All the arguments passed when executing the command
     * @param array $inputs    The (optional) answers given to the command when it asks for the value of the missing arguments
     */
    private function executeCommand(array $arguments, array $inputs = [])
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        /** @var Registry $doctrine */
        $doctrine = $container->get('doctrine');
        $command = new CreateClientCommand($doctrine->getManager(), $container->get('security.password_encoder'), $doctrine->getRepository(Client::class));
        $command->setApplication(new Application(self::$kernel));
        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);
    }
}
