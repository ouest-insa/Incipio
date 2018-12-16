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

use App\Entity\Treso\BaseURSSAF;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadBaseURSSAFData extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $base = [
            2017 => 39.04,
            2016 => 38.68,
            2015 => 38.44,
            2014 => 38.12,
            2013 => 37.72,
            2012 => 36.88,
            2011 => 36,
            2010 => 35.44,
            2009 => 34.84,
            2008 => 33.76,
            2007 => 33.08,
        ];
        for ($y = 2009; $y < 2018; ++$y) {
            $baseURSSAF = new BaseURSSAF();
            if (array_key_exists($y, $base)) {
                $baseURSSAF->setBaseURSSAF($base[$y])->setDateDebut(new \DateTime("$y-01-01"))->setDateFin(new \DateTime("$y-12-31"));
                $manager->persist($baseURSSAF);
            }
        }
        if (!$manager->getRepository(BaseURSSAF::class)->findBy([
            'dateDebut' => $baseURSSAF->getDateDebut(),
            ])) {
            $manager->flush();
        }
    }
}
