<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAdminData extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $username = getenv('SUPER_ADMIN_USERNAME');
        $password = getenv('SUPER_ADMIN_PASSWORD');
        $mail = getenv('TECHNICAL_TO');

        $su = $manager->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$su) {
            $su = new User();
        }

        $su->setUsername($username); //mettre le login de l'admin
        $su->setPlainPassword($password); //mettre le mdp de l'admin
        $su->setEmail($mail);
        $su->setEnabled(true);
        $su->setRoles(['ROLE_SUPER_ADMIN']);

        //$manager->persist($personne);
        $manager->persist($su);
        $manager->flush();
    }
}
