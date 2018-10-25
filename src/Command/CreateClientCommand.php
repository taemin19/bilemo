<?php

namespace App\Command;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * A console command that creates clients and stores them in the database
 */
class CreateClientCommand extends Command
{
    /**
     * Make the command lazily loaded.
     * @var string
     */
    protected static $defaultName = 'app:create-client';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ClientRepository
     */
    private $clients;

    /**
     * CreateClientCommand constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param ClientRepository $clients
     */
    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, ClientRepository $clients)
    {
        $this->entityManager = $em;
        $this->passwordEncoder = $encoder;
        $this->clients = $clients;

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Create clients and stores them in the database')
            ->setHelp('This command allows you to create a client.')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the new client')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new client')
            ->addArgument('password', InputArgument::OPTIONAL, 'The password of the new client')
        ;
    }

    /**
     * Initialize properties based on the input arguments and options.
     * @see Command
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Check if some of the options/arguments are missing and interactively ask the user for those values.
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getArgument('name') && null !== $input->getArgument('username') && null !== $input->getArgument('password')) {
            return;
        }

        $this->io->title('Create Client Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:create-client name username password',
            '',
        ]);

        // ask for the name if it's not defined
        $name = $input->getArgument('name');
        if (null !== $name) {
            $this->io->text(' > <info>Name</info>: '.$name);
        } else {
            $name = $this->io->ask('Name');
            $input->setArgument('name', $name);
        }

        // ask for the username if it's not defined
        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Username</info>: '.$username);
        } else {
            $username = $this->io->ask('Username');
            $input->setArgument('username', $username);
        }

        // ask for the password if it's not defined
        $password = $input->getArgument('password');
        if (null !== $password) {
            $this->io->text(' > <info>Password</info>: '.$password);
        } else {
            $password = $this->io->ask('Password');
            $input->setArgument('password', $password);
        }
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        // validate the client data
        $this->validateClientData($name, $username, $password);

        // ask confirmation
        if (!$this->io->confirm('Confirm the client creation?', true)) {
            return;
        }

        // create the client and encode its password
        $client = new Client();
        $client->setName($name);
        $client->setUsername($username);
        $client->setPassword($this->passwordEncoder->encodePassword($client, $password));

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $this->io->success('Client successfully generated!');
    }

    protected function validateClientData($name, $username, $password)
    {
        // check if a client with the same username already exists
        $existingClient = $this->clients->findOneBy(['username' => $username]);
        if (null !== $existingClient) {
            throw new RuntimeException(sprintf('There is already a client registered with the "%s" username.', $username));
        }

        // validate name
        if (empty($name)) {
            throw new RuntimeException(sprintf('The name can not be empty.'));
        }
        if (mb_strlen(trim($name)) > 25) {
            throw new RuntimeException(sprintf('The name must be at most 25 characters long.'));
        }

        // validate username
        if (empty($username)) {
            throw new RuntimeException(sprintf('The username can not be empty.'));
        }
        if (1 !== preg_match('/^[a-z_]+$/', $username)) {
            throw new RuntimeException(sprintf('The username must contain only lowercase latin characters and underscores.'));
        }
        if (mb_strlen(trim($username)) > 25) {
            throw new RuntimeException(sprintf('The username must be at most 25 characters long.'));
        }

        // validate password
        if (empty($password)) {
            throw new RuntimeException(sprintf('The password can not be empty.'));
        }
        if (mb_strlen(trim($password)) < 6) {
            throw new RuntimeException(sprintf('The password must be at least 6 characters long.'));
        }
        if (mb_strlen(trim($password)) > 64) {
            throw new RuntimeException(sprintf('The password must be at most 64 characters long.'));
        }
    }
}
