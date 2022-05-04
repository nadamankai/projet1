<?php

namespace App\DataFixtures;

use App\Entity\Job;
use App\Entity\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProfileFixure extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create();

        for($i =0;$i<20;$i++){
            $profile = new Profile();
           $profile->setUrl('https://www.facebook.com/');
           $profile->setRs("facebook");
    $manager->persist($profile);
    $manager->flush();
    }
    }
}
