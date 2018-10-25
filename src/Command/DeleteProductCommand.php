<?php

namespace App\Command;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A console command that deletes products from the database
 */
class DeleteProductCommand extends Command
{
    /**
     * Make the command lazily loaded.
     * @var string
     */
    protected static $defaultName = 'app:delete-product';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProductRepository
     */
    private $products;

    public function __construct(EntityManagerInterface $em, ProductRepository $products)
    {
        $this->entityManager = $em;
        $this->products = $products;

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Deletes products from the database')
            ->setHelp('This command allows you to delete a product.')
            ->addArgument('id', InputArgument::OPTIONAL, 'The id of the product')
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
        if (null !== $input->getArgument('id')) {
            return;
        }

        $this->io->title('Delete Product Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:delete-product id',
            '',
        ]);

        // ask for the username if it's not defined
        $id = $input->getArgument('id');
        if (null !== $id) {
            $this->io->text(' > <info>Id</info>: '.$id);
        } else {
            $id = $this->io->ask('Id');
            $input->setArgument('id', $id);
        }
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        // check if a product with the same id exists
        $product = $this->products->find($id);
        if (null == $product) {
            throw new RuntimeException(sprintf('Product with id "%s" not found.', $id));
        }

        // ask confirmation
        if (!$this->io->confirm(sprintf('Are you sure to delete the product "%s" (id: %s)?',$product->getModel(), $id), true)) {
            return;
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        $this->io->success(sprintf('Product "%s" (id: %s) was successfully deleted.',$product->getModel(), $id));
    }
}
