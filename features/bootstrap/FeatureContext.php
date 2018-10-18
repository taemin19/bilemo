<?php

use App\Entity\Product;
use App\Entity\User;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Contains the definitions of the steps used by the features
 */
class FeatureContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var SchemaTool
     */
    private $schemaTool;

    /**
     * @var array
     */
    private $classes;

    public function __construct(KernelInterface $kernel, ManagerRegistry $doctrine)
    {
        $this->kernel = $kernel;
        $this->doctrine = $doctrine;
        $manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($manager);
        $this->classes = $manager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * @BeforeScenario
     */
    public function clearDatabase()
    {
        $this->schemaTool->dropSchema($this->classes);
        $this->doctrine->getManager()->clear();
        $this->schemaTool->createSchema($this->classes);
    }

    /**
     * @param TableNode $table
     * @Given the following products exist:
     */
    public function theFollowingProductsExist(TableNode $table)
    {
        $em = $this->doctrine->getManager();

        foreach ($table->getHash() as $productHash) {
            $product = new Product();
            $product->setModel($productHash['model']);
            $product->setBrand($productHash['brand']);
            $product->setStorage($productHash['storage']);
            $product->setColor($productHash['color']);
            $product->setPrice($productHash['price']);
            $product->setDescription($productHash['description']);
            $em->persist($product);
        }
        
        $em->flush();
    }

    /**
     * @param TableNode $table
     * @Given the following users exist:
     */
    public function theFollowingUsersExist(TableNode $table)
    {
        $em = $this->doctrine->getManager();

        foreach ($table->getHash() as $userHash) {
            $user = new User();
            $user->setFirstname($userHash['firstname']);
            $user->setLastname($userHash['lastname']);
            $user->setEmail($userHash['email']);
            $em->persist($user);
        }
        $em->flush();
    }
}
