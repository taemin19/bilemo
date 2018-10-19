<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClientFixtures extends Fixture
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
     * ClientFixtures constructor.
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
        // create 2 clients
        for ($i = 1; $i <= 2; $i++)
        {
            $client = new Client();
            $client->setName($this->faker->unique()->randomElement(['SFR','ORANGE']));
            $client->setUsername('client'.$i);
            $client->setPassword($this->encoder->encodePassword($client, 'client'.$i));

            $manager->persist($client);
        }

        $manager->flush();
    }
}
