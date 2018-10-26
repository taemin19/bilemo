<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A console command that creates products and stores them in the database
 */
class CreateProductCommand extends Command
{
    /**
     * Make the command lazily loaded.
     * @var string
     */
    protected static $defaultName = 'app:create-product';

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CreateProductCommand constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;

        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Create products and stores them in the database')
            ->setHelp('This command allows you to create a product.')
            ->addArgument('model', InputArgument::OPTIONAL, 'The model of the new product')
            ->addArgument('brand', InputArgument::OPTIONAL, 'The brand of the new product')
            ->addArgument('storage', InputArgument::OPTIONAL, 'The storage of the new product')
            ->addArgument('color', InputArgument::OPTIONAL, 'The color of the new product')
            ->addArgument('price', InputArgument::OPTIONAL, 'The price of the new product')
            ->addArgument('description', InputArgument::OPTIONAL, 'The description of the new product')
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
        if (null !== $input->getArgument('model') && null !== $input->getArgument('brand') && null !== $input->getArgument('storage') && null !== $input->getArgument('color') && null !== $input->getArgument('price') && null !== $input->getArgument('description')) {
            return;
        }

        $this->io->title('Create Product Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:create-product model brand storage color price description',
            '',
        ]);

        // ask for the model if it's not defined
        $model = $input->getArgument('model');
        if (null !== $model) {
            $this->io->text(' > <info>Model</info>: '.$model);
        } else {
            $model = $this->io->ask('Model');
            $input->setArgument('model', $model);
        }

        // ask for the brand if it's not defined
        $brand = $input->getArgument('brand');
        if (null !== $brand) {
            $this->io->text(' > <info>Brand</info>: '.$brand);
        } else {
            $brand = $this->io->ask('Brand');
            $input->setArgument('brand', $brand);
        }

        // ask for the storage if it's not defined
        $storage = $input->getArgument('storage');
        if (null !== $storage) {
            $this->io->text(' > <info>Storage</info>: '.$storage);
        } else {
            $storage = $this->io->ask('Storage');
            $input->setArgument('storage', $storage);
        }

        // ask for the color if it's not defined
        $color = $input->getArgument('color');
        if (null !== $color) {
            $this->io->text(' > <info>Color</info>: '.$color);
        } else {
            $color = $this->io->ask('Color');
            $input->setArgument('color', $color);
        }

        // ask for the price if it's not defined
        $price = $input->getArgument('price');
        if (null !== $price) {
            $this->io->text(' > <info>Price</info>: '.$price);
        } else {
            $price = $this->io->ask('Price');
            $input->setArgument('price', $price);
        }

        // ask for the description if it's not defined
        $description = $input->getArgument('description');
        if (null !== $description) {
            $this->io->text(' > <info>Description</info>: '.$description);
        } else {
            $description = $this->io->ask('Description');
            $input->setArgument('description', $description);
        }
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = $input->getArgument('model');
        $brand = $input->getArgument('brand');
        $storage = $input->getArgument('storage');
        $color = $input->getArgument('color');
        $price = $input->getArgument('price');
        $description = $input->getArgument('description');

        // validate the product data
        $this->validateProductData($model, $brand, $storage, $color, $price, $description);

        // create the product
        $product = new Product();
        $product->setModel($model);
        $product->setBrand($brand);
        $product->setStorage($storage);
        $product->setColor($color);
        $product->setPrice($price);
        $product->setDescription($description);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->io->success('Product successfully generated!');
    }

    protected function validateProductData($model, $brand, $storage, $color, $price, $description)
    {
        // validate model
        if (empty($model)) {
            throw new RuntimeException(sprintf('The model can not be empty.'));
        }
        if (mb_strlen(trim($model)) > 50) {
            throw new RuntimeException(sprintf('The model must be at most 50 characters long.'));
        }

        // validate brand
        if (empty($brand)) {
            throw new RuntimeException(sprintf('The brand can not be empty.'));
        }
        if (mb_strlen(trim($brand)) > 25) {
            throw new RuntimeException(sprintf('The brand must be at most 25 characters long.'));
        }

        // validate storage
        if (empty($storage)) {
            throw new RuntimeException(sprintf('The storage can not be empty.'));
        }
        if (!(int)$storage) {
            throw new RuntimeException(sprintf('The storage must be a number.'));
        }

        // validate color
        if (empty($color)) {
            throw new RuntimeException(sprintf('The color can not be empty.'));
        }
        if (mb_strlen(trim($color)) > 25) {
            throw new RuntimeException(sprintf('The color must be at most 25 characters long.'));
        }

        // validate price
        if (empty($price)) {
            throw new RuntimeException(sprintf('The price can not be empty.'));
        }
        if (!(int)$price) {
            throw new RuntimeException(sprintf('The price must be a number.'));
        }

        // validate description
        if (empty($description)) {
            throw new RuntimeException(sprintf('The description can not be empty.'));
        }
    }
}
