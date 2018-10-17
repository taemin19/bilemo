<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
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
