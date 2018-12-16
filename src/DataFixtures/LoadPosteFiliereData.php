<?php

/*
   Alexandre Couedelo @ 2015-02-17 20:15:24
    Importé depuis Emagine/incipio
 */

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Filiere;
use App\Entity\Poste;

class LoadPosteFiliereData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $postes = [
            //Bureau
            'Président',
            'Président par procuration',
            'Vice-président',
            'Trésorier',
            'Suiveur Manager Qualité',
            'Secrétaire général',
            //ca
            'Manager Qualité-Tréso',
            'Vice-Trésorier',
            'Binome Qualité',
            'Respo. Communication',
            'Respo. SI',
            "Respo. Dev'Co",
            //Membre
            'membre',
            'Intervenant',
            'Chef de Projet',
        ];

        foreach ($postes as $poste) {
            $p = new Poste();
            $p->setIntitule($poste);
            $p->setDescription('a completer');

            $manager->persist($p);
        }

        $filiere = new Filiere();
        $filiere->setNom('Filière d\'exemple');
        $filiere->setDescription('Filière par défault, à éditer après l\'installation');

        $manager->flush();
    }
}
