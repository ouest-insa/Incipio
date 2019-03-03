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

use App\Entity\Treso\CotisationURSSAF;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCotisationURSSAFData extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $cotisations = [];

        /*
         * BV TYPE 2014
         */
        $cotisations[] = [
            'libelle' => 'C.R.D.S. + CSG non déductible',
            'isBaseUrssaf' => true,
            'tauxJE' => 0,
            'tauxEtu' => 0.029,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'C.S.G.',
            'isBaseUrssaf' => true,
            'tauxJE' => 0,
            'tauxEtu' => 0.051,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Assurance maladie',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.1280,
            'tauxEtu' => 0.0075,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Contribution solidarité autonomie',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0030,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Assurance vieillesse déplafonnée',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0175,
            'tauxEtu' => 0.0025,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Assurance vieillesse plafonnée TA',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0845,
            'tauxEtu' => 0.0680,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Accident du travail',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0150,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Allocations familliales',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0525,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Fond National d\'Aide au Logement',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0010,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Versement Transport',
            'isBaseUrssaf' => true,
            'tauxJE' => 0,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Assurance chômage',
            'isBaseUrssaf' => false,
            'tauxJE' => 0.0400,
            'tauxEtu' => 0.0240,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'AGS',
            'isBaseUrssaf' => false,
            'tauxJE' => 0.030,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2014-01-01'),
            'dateFin' => new \DateTime('2014-12-31'),
            ];

        /*
         * COTISATIONS 2017
         */
        $cotisations[] = [
            'libelle' => 'C.R.D.S. + CSG non déductible',
            'isBaseUrssaf' => true,
            'tauxJE' => 0,
            'tauxEtu' => 0.029,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'C.S.G.',
            'isBaseUrssaf' => true,
            'tauxJE' => 0,
            'tauxEtu' => 0.051,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Assurance maladie',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.1289,
            'tauxEtu' => 0.01,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Contribution solidarité autonomie',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.003,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Assurance vieillesse déplafonnée',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.019,
            'tauxEtu' => 0.004,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Assurance vieillesse plafonnée TA',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0855,
            'tauxEtu' => 0.069,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Accident du travail',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.012,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Allocations familliales',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0525,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Fond National d\'Aide au Logement',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.001,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Versement Transport',
            'isBaseUrssaf' => true,
            'tauxJE' => 0,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Contribution pour le financement des organisations professionnelles',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.00016,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Cotisation de base au titre de la pénibilité',
            'isBaseUrssaf' => true,
            'tauxJE' => 0.0001,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'Assurance chômage',
            'isBaseUrssaf' => false,
            'tauxJE' => 0.04,
            'tauxEtu' => 0.024,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        $cotisations[] = [
            'libelle' => 'AGS',
            'isBaseUrssaf' => false,
            'tauxJE' => 0.002,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-01-01'),
            'dateFin' => new \DateTime('2017-06-30'),
            ];

        $cotisations[] = [
            'libelle' => 'AGS',
            'isBaseUrssaf' => false,
            'tauxJE' => 0.0015,
            'tauxEtu' => 0,
            'dateDebut' => new \DateTime('2017-07-01'),
            'dateFin' => new \DateTime('2017-12-31'),
            ];

        foreach ($cotisations as $cotisation) {
            $cotisationURSSAF = new CotisationURSSAF();

            $cotisationURSSAF
                ->setDateDebut($cotisation['dateDebut'])
                ->setDateFin($cotisation['dateFin'])
                ->setSurBaseURSSAF($cotisation['isBaseUrssaf'])
                ->setLibelle($cotisation['libelle'])
                ->setTauxPartEtu($cotisation['tauxEtu'])
                ->setTauxPartJE($cotisation['tauxJE']);

            if (!$manager->getRepository(CotisationURSSAF::class)->findBy([
                'dateDebut' => $cotisationURSSAF->getDateDebut(),
                'dateFin' => $cotisationURSSAF->getDateFin(),
                'libelle' => $cotisationURSSAF->getLibelle(),
            ])) {
                $manager->persist($cotisationURSSAF);
            }
        }
        $manager->flush();
    }
}
