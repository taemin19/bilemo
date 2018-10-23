<?php

namespace App\Command;

use App\Entity\Client;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateClientCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * CreateClientCommand constructor.
     * @param ManagerRegistry $doctrine
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(ManagerRegistry $doctrine, UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:create-client')
            ->setDescription('Create a new client')
            ->setHelp('This command allows you to create a client.')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Client Creator',
            '==============',
            ''
        ]);

        $helper = $this->getHelper('question');

        $question = new Question('Please enter the name:');
        $name = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the username:');
        $username = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the password:');
        $password = $helper->ask($input, $output, $question);

        $output->writeln('');
        $confirmation = new ConfirmationQuestion('Confirm the client creation?', true);
        if (!$helper->ask($input, $output, $confirmation)) {
            return;
        }

        $em = $this->doctrine->getManager();
        $client = new Client();
        $client->setName($name);
        $client->setUsername($username);
        $client->setPassword($this->encoder->encodePassword($client, $password));
        $em->persist($client);
        $em->flush();

        $output->writeln('<info>Client successfully generated!</info>');
    }
}
