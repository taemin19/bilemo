<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    /**
     * @var Factory
     */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        // create 5 users
        for ($i = 1; $i <= 5; $i++)
        {
            $user = new User();
            $user->setFirstname($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setEmail($this->faker->email);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
