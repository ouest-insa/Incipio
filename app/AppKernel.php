<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            /****************************************
             *                Symfony                *
             *****************************************/
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            /****************************************
             *            Vendor - Doctrine            *
             *****************************************/
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            /****************************************
             *            Vendor - FOS                *
             *****************************************/
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\CommentBundle\FOSCommentBundle(),
            new FOS\RestBundle\FOSRestBundle(),

            new Ob\HighchartsBundle\ObHighchartsBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),

            /****************************************
             *                M-GaTE                    *
             *****************************************/
            new Mgate\UserBundle\MgateUserBundle(),
            new Mgate\PubliBundle\MgatePubliBundle(),
            new Mgate\DashboardBundle\MgateDashboardBundle(),
            new Mgate\StatBundle\MgateStatBundle(),
            new Mgate\TresoBundle\MgateTresoBundle(),
            new Mgate\FormationBundle\MgateFormationBundle(),
            new Mgate\PersonneBundle\MgatePersonneBundle(),
            new Mgate\CommentBundle\MgateCommentBundle(),
            new Mgate\SuiviBundle\MgateSuiviBundle(),
            new EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle(),
            new N7consulting\RhBundle\N7consultingRhBundle(),
            new N7consulting\PrivacyBundle\N7consultingPrivacyBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
