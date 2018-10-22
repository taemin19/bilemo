<?php

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behatch\Context\RestContext;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    /**
     * @var JWTManager
     */
    private $jwtManager;

    /**
     * @var RestContext
     */
    private $restContext;

    /**
     * @var Client
     */
    private $currentClient;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(
        KernelInterface $kernel,
        ManagerRegistry $doctrine,
        JWTManager $jwtManager,
        UserPasswordEncoderInterface $encoder)
    {
        $this->kernel = $kernel;
        $this->doctrine = $doctrine;
        $manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($manager);
        $this->classes = $manager->getMetadataFactory()->getAllMetadata();
        $this->jwtManager = $jwtManager;
        $this->encoder = $encoder;
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
     * @param BeforeScenarioScope $scope
     * @BeforeScenario @loginAsClient1
     */
    public function login(BeforeScenarioScope $scope)
    {
        $client = new Client();
        $client->setName('Client1');
        $client->setUsername('client1');
        $client->setPassword('client1');

        $this->currentClient = $client;

        $em = $this->doctrine->getManager();
        $em->persist($client);
        $em->flush();

        $token = $this->jwtManager->create($client);
        $this->restContext = $scope->getEnvironment()->getContext(RestContext::class);
        $this->restContext->iAddHeaderEqualTo('Authorization', "Bearer $token");
    }

    /**
     * @param TableNode $table
     * @Given the following clients exist:
     */
    public function theFollowingClientsExist(TableNode $table)
    {
        $em = $this->doctrine->getManager();

        foreach ($table->getHash() as $clientHash) {
            $client = new Client();
            $client->setName($clientHash['name']);
            $client->setUsername($clientHash['username']);
            $client->setPassword($this->encoder->encodePassword($client, $clientHash['password']));
            $em->persist($client);
        }

        $em->flush();
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
