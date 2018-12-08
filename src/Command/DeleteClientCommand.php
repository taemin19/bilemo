<?php

namespace App\Command;

use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A console command that deletes clients from the database
 */
class DeleteClientCommand extends Command
{
    /**
     * Make the command lazily loaded.
     * @var string
     */
    protected static $defaultName = 'app:delete-client';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ClientRepository
     */
    private $clients;

    public function __construct(EntityManagerInterface $em, ClientRepository $clients)
    {
        $this->entityManager = $em;
        $this->clients = $clients;

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Deletes clients from the database')
            ->setHelp('This command allows you to delete a client.')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the client')
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
        if (null !== $input->getArgument('username')) {
            return;
        }

        $this->io->title('Delete Client Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:delete-client username',
            '',
        ]);

        // ask for the username if it's not defined
        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Username</info>: '.$username);
        } else {
            $username = $this->io->ask('Username');
            $input->setArgument('username', $username);
        }
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        // check if a client with the same username exists
        $client = $this->clients->findOneBy(['username' => $username]);
        if (null === $client) {
            throw new RuntimeException(sprintf('Client with username "%s" not found.', $username));
        }

        // ask confirmation
        if (!$this->io->confirm(sprintf('Are you sure to delete the client "%s" (username: %s)?',$client->getName(), $username), true)) {
            return;
        }

        $this->entityManager->remove($client);
        $this->entityManager->flush();

        $this->io->success(sprintf('Client "%s" (username: %s) was successfully deleted.',$client->getName(), $username));
    }
}
