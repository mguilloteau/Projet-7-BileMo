<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class PhoneFixtures extends Fixture
{


    public function load(ObjectManager $manager)
    {
				$faker = Faker\Factory::create("fr-FR");

				for($i = 0; $i<=30; $i++) {
					$phone = new Phone();
					$phone->setName(ucfirst($faker->word))
						->setDescription($faker->paragraph(3, true))
						->setPrice($faker->numberBetween(400,1300))
						->setColor($faker->colorName)
					;

					$manager->persist($phone);
					$manager->flush();
				}
    }
}
