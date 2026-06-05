<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com')
            ->setFirstName('Admin')
            ->setLastName('Admin')
            ->setRoles([User::ROLE_ADMIN])
            ->setEmailConfirmed(true)
            ->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $moderator = new User();
        $moderator->setEmail('moderator@example.com')
            ->setFirstName('Moderator')
            ->setLastName('Moderator')
            ->setRoles([User::ROLE_MODERATOR])
            ->setEmailConfirmed(true)
            ->setPassword($this->hasher->hashPassword($moderator, 'mod123'));
        $manager->persist($moderator);

        $user = new User();
        $user->setEmail('user@example.com')
            ->setFirstName('User')
            ->setLastName('User')
            ->setRoles([])
            ->setEmailConfirmed(true)
            ->setPassword($this->hasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        $manager->flush();
    }
}
