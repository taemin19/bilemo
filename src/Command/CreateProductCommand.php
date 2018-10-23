<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CreateProductCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * CreateProductCommand constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('app:create-product')
            ->setDescription('Create a new product')
            ->setHelp('This command allows you to create a product.')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Product Creator',
            '==============',
            ''
        ]);

        $helper = $this->getHelper('question');

        $question = new Question('Please enter the model:');
        $model = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the brand:');
        $brand = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the storage:');
        $storage = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the color:');
        $color = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the price:');
        $price = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the description:');
        $description = $helper->ask($input, $output, $question);

        $output->writeln('');
        $confirmation = new ConfirmationQuestion('Confirm the product creation?', true);
        if (!$helper->ask($input, $output, $confirmation)) {
            return;
        }

        $em = $this->doctrine->getManager();
        $product = new Product();
        $product->setModel($model);
        $product->setBrand($brand);
        $product->setStorage($storage);
        $product->setColor($color);
        $product->setPrice($price);
        $product->setDescription($description);
        $em->persist($product);
        $em->flush();

        $output->writeln('<info>Product successfully generated!</info>');
    }
}
