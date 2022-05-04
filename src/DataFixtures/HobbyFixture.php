<?php

namespace App\DataFixtures;

use App\Entity\Hobby;
use App\Entity\Job;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class HobbyFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for($i =0;$i<20;$i++){
            $hobby = new Hobby();
            $hobby->setDesignation($faker->domainName);
    $manager->persist($hobby);
    $manager->flush();
    }
    }
}
