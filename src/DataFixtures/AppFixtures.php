<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var Factory
     */
    private $faker;

    /**
     * AppFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->faker = Factory::create();
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadClients($manager);
        $this->loadUsers($manager);
        $this->loadProducts($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadClients(ObjectManager $manager)
    {
        // create 2 clients
        for ($i = 1; $i <= 2; $i++)
        {
            $client = new Client();
            $client->setName($this->faker->unique()->randomElement(['SFR','ORANGE']));
            $client->setUsername('client'.$i);
            $client->setPassword($this->encoder->encodePassword($client, 'client'.$i));

            // add object reference
            $this->addReference('client'.$i, $client);

            $manager->persist($client);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadUsers(ObjectManager $manager)
    {
        // get object references 'client1' and 'client2'
        $client1 = $this->getReference('client1');
        $client2 = $this->getReference('client2');

        // create 5 users for client1
        for ($i = 1; $i <= 5; $i++)
        {
            $user = new User();
            $user->setFirstname($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setEmail($this->faker->email);
            $user->setClient($client1);

            $manager->persist($user);
        }

        // create 5 users for client2
        for ($i = 1; $i <= 5; $i++)
        {
            $user = new User();
            $user->setFirstname($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setEmail($this->faker->email);
            $user->setClient($client2);

            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadProducts(ObjectManager $manager)
    {
        // create 5 products
        for ($i = 1; $i <= 5; $i++)
        {
            $product = new Product();
            $product->setModel('product'.$i);
            $product->setBrand($this->faker->randomElement(['Apple','Samsung','Huawei','LG']));
            $product->setStorage($this->faker->randomElement(['16','32','64']));
            $product->setColor($this->faker->safeColorName);
            $product->setPrice($this->faker->randomFloat(2, 600, 1200));
            $product->setDescription($this->faker->text(100));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
