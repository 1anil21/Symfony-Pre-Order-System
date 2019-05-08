<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Create Products
        for ($i = 1; $i < 10; $i++) {
            $product = new Product();
            $product->setName('iPhone '.$i);
            $product->setPrice($i * 500);
            $manager->persist($product);
        }

        // Create Admin User
        $user = new User();
        $user->setFullName("admin");
        $user->setUsername("admin");
        $user->setEmail("admin@admin.com");
        $encodedPass = $this->encoder->encodePassword($user, "admin");
        $user->setPassword($encodedPass);
        $manager->persist($user);

        $manager->flush();
    }
}