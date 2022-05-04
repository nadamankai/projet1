<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class Personne extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

for($i =0;$i<20;$i++){
    $pers = new \App\Entity\Personne();
    $pers->setNom($faker->name);
    $pers->setFirstname($faker->firstName);
    $pers->setAge($faker->numberBetween(5,65));
    $manager->persist($pers);
    $manager->flush();
}

    }
}
