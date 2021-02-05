<?php

	namespace App\DataFixtures;

	use App\Entity\Customer;
	use App\Entity\User;
	use Doctrine\Bundle\FixturesBundle\Fixture;
	use Doctrine\Persistence\ObjectManager;
	use Faker;
	use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

	class CustomerFixtures extends Fixture {

		private $encodePassword;

		public function __construct(UserPasswordEncoderInterface $encodePassword) {
			$this->encodePassword = $encodePassword;
		}

		public function load(ObjectManager $manager)
		{

			$faker = Faker\Factory::create("fr-FR");

			$customers= [];

			for($i=0;$i<3;$i++) {

				$customer = new Customer();
				$customer->setUsername($faker->userName)
					->setPassword($this->encodePassword->encodePassword($customer,"test123"))
					->setRoles(["ROLE_USER"])
				;
				$customers[] = $customer;

				$manager->persist($customer);
				$manager->flush();
			}

			for($j=0;$j<=rand(24, 30);$j++) {
				$user = new User();
				$user->setUsername($faker->userName)
					->setName($faker->firstName)
					->setSurname($faker->lastName)
					->setEmail($faker->email)
					->setCustomer($faker->randomElement($customers))
				;

				$manager->persist($user);
				$manager->flush();
			}
		}
	}