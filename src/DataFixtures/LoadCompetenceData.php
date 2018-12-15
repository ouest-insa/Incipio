<?php

namespace N7consulting\RhBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use N7consulting\RhBundle\Entity\Competence;

class LoadCompetenceData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $competences = [
            'PHP',
            'HTML',
            'CSS',
            'Symfony 2',
            'Javascript',
            'Jquery',
            'Bootstrap',
            'Android',
            'Java',
            'Python',
            'Wordpress',
            'Phonegap / Cordova',
            'IOS',
        ];

        foreach ($competences as $competence) {
            $c = new Competence();
            $c->setNom($competence);

            $manager->persist($c);
        }
        $manager->flush();
    }
}
