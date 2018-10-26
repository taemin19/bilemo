<?php

namespace App\Tests\Command;

use App\Command\CreateProductCommand;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CreateProductCommandTest extends KernelTestCase
{
    private $productData = [
        'model' => 'Product',
        'brand' => 'Brand',
        'storage' => '64',
        'color' => 'red',
        'price' => '899.99',
        'description' => 'description',
    ];

    /**
     * This test provides all the arguments required by the command, so the
     * command runs non-interactively and it won't ask for any argument.
     */
    public function testCreateProductNonInteractive()
    {
        $input = $this->productData;

        $this->executeCommand($input);
        $this->assertProductCreated();
    }

    /**
     * This test doesn't provide all the arguments required by the command, so
     * the command runs interactively and it will ask for the value of the missing
     * arguments.
     */
    public function testCreateProductInteractive()
    {
        $this->executeCommand(
        // these are the missing arguments
            [],
            // these are the responses given to the questions asked by the command
            // to get the value of the missing required arguments
            array_values($this->productData)
        );
        $this->assertProductCreated();
    }

    /**
     * This helper method checks that the product was correctly created and saved
     * in the database.
     */
    private function assertProductCreated()
    {
        $container = self::$kernel->getContainer();
        /** @var Product $product */
        $product = $container->get('doctrine')->getRepository(Product::class)->find(1);
        $this->assertNotNull($product);
        $this->assertSame($this->productData['model'], $product->getModel());
        $this->assertSame($this->productData['brand'], $product->getBrand());
        $this->assertSame((int)$this->productData['storage'], $product->getStorage());
        $this->assertSame($this->productData['color'], $product->getColor());
        $this->assertSame((float)$this->productData['price'], $product->getPrice());
        $this->assertSame($this->productData['description'], $product->getDescription());
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
        $command = new CreateProductCommand($doctrine->getManager());
        $command->setApplication(new Application(self::$kernel));
        $commandTester = new CommandTester($command);
        $commandTester->setInputs($inputs);
        $commandTester->execute($arguments);
    }
}
