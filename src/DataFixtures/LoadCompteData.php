<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\TresoBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mgate\TresoBundle\Entity\Compte;

class LoadCompteData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $comptes = [
            622600 => 'Honoraires BV',
            645100 => 'Cotisations à l\'Urssaf',
            705000 => 'Etudes',
            708500 => 'Ports et frais accessoires facturés',
            419100 => 'Clients - Avances et acomptes reçus sur commandes',
        ];

        foreach ($comptes as $key => $value) {
            $compte = new Compte();
            $compte->setCategorie(false)->setLibelle($value)->setNumero($key);
            $manager->persist($compte);
        }
        if (!$manager->getRepository('MgateTresoBundle:Compte')->findBy(['numero' => $compte->getNumero()])) {
            $manager->flush();
        }
    }
}
