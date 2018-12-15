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
use Mgate\TresoBundle\Entity\BaseURSSAF;

class LoadBaseURSSAFData implements FixtureInterface
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
        if (!$manager->getRepository('MgateTresoBundle:BaseURSSAF')->findBy([
            'dateDebut' => $baseURSSAF->getDateDebut(),
            ])) {
            $manager->flush();
        }
    }
}
