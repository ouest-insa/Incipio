<?php

namespace Mgate\PersonneBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mgate\StatBundle\Entity\Indicateur;

class LoadIndicateurData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /************************************************
         * 			Indicateurs Suivi d'études			*
         * ********************************************** */

        // Taux d'avenant par mandat = Rate EtudeAvecAvenant/NombreEtude
        $tauxAvenant = new Indicateur();
        $tauxAvenant->setTitre('Taux d\'avenant par mandat')
            ->setMethode('getTauxDAvenantsParMandat');

        // Cammembert étude selon domaine de compétence
        // TODO : Selectionner un mandat default getMaxMandat

        $nombreEtudes = new Indicateur();
        $nombreEtudes->setTitre('Nombre d\'études par mandat')
            ->setMethode('getNombreEtudes');

        $retardSurEtude = new Indicateur();
        $retardSurEtude->setTitre('Nombre de jours de retard')
            ->setMethode('getRetardParMandat');

        /************************************************
         * 			Indicateurs Gestion Asso			*
         * ********************************************** */
        // Nombre d'intervenants par promo
        $ressourcesHumaines = new Indicateur();
        $ressourcesHumaines->setTitre('Nombre d\'intervenants par Promo')
            ->setMethode('getIntervenantsParPromo');

        // Nombre d'e membre par promo
        $membresParPromo = new Indicateur();
        $membresParPromo->setTitre('Nombre de Membres par Promo')
            ->setMethode('getMembresParPromo');

        // Nombre de cotisant en continu
        $membres = new Indicateur();
        $membres->setTitre('Nombre de Membres')
            ->setMethode('getNombreMembres');

        /************************************************
         * 				Indicateurs RFP					*
         * ********************************************** */
        $nombreDeFormationsParMandat = new Indicateur();
        $nombreDeFormationsParMandat->setTitre('Nombre de formations théorique par mandat')
            ->setMethode('getNombreFormationsParMandat');

        $presenceAuxFormationsTimed = new Indicateur();
        $presenceAuxFormationsTimed->setTitre('Nombre de présents aux formations')
            ->setMethode('getNombreDePresentFormationsTimed');

        /************************************************
         * 			Indicateurs Trésorerie 			*
         * ********************************************** */
        //Chiffre d'affaires en fonction du temps sur les Mandats
        $chiffreAffaires = new Indicateur();
        $chiffreAffaires->setTitre('Evolution du Chiffre d\'Affaires')
            ->setMethode('getCA');

        //Chiffre d'affaires par mandat
        $chiffreAffairesMandat = new Indicateur();
        $chiffreAffairesMandat->setTitre('Evolution du Chiffre d\'Affaires par Mandat')
            ->setMethode('getCAM');

        //Dépense HT par mandat
        $sortieNFFA = new Indicateur();
        $sortieNFFA->setTitre('Evolution des dépenses par mandats')
            ->setMethode('getSortie');

        //Répartition des dépenses sur le mandat
        $repartitionSortieNFFA = new Indicateur();
        $repartitionSortieNFFA->setTitre('Répartition des dépenses sur le mandat')
            ->setMethode('getRepartitionSorties');

        /************************************************
         * 		Indicateurs Prospection Commerciale		*
         * ********************************************** */
        // Provenance des études (tous mandats) par type de client
        // TODO : selectionner un mandat default getMaxMandat -1 = tous les mandats
        $repartitionClient = new Indicateur();
        $repartitionClient->setTitre('Provenance de nos études par type de Client (tous mandats)')
            ->setMethode('getRepartitionClientParNombreDEtude');

        // Provenance du chiffre d'Affaires (tous mandats) par type de client
        // TODO : selectionner un mandat default getMaxMandat -1 = tous les mandats
        $repartitionCAClient = new Indicateur();
        $repartitionCAClient->setTitre('Provenance du chiffre d\'Affaires par type de Client (tous mandats)')
            ->setMethode('getRepartitionClientSelonChiffreAffaire');

        // Provenance des études (tous mandats) par source de prospection
        // TODO : selectionner un mandat default getMaxMandat -1 = tous les mandats
        $repartitionSourceProspectionClient = new Indicateur();
        $repartitionSourceProspectionClient->setTitre('Provenance de nos études par source de prospection (tous mandats)')
            ->setMethode('getSourceProspectionParNombreDEtude');

        // Provenance du chiffre d'Affaires (tous mandats) par source de prospection
        // TODO : selectionner un mandat default getMaxMandat -1 = tous les mandats
        $repartitionSourceProspectionCAClient = new Indicateur();
        $repartitionSourceProspectionCAClient->setTitre('Provenance du chiffre d\'Affaires par source de prospection (tous mandats)')
            ->setMethode('getSourceProspectionSelonChiffreAffaire');

        // Taux de fidélisation
        $clientFidel = new Indicateur();
        $clientFidel->setTitre('Taux de fidélisation')
            ->setMethode('getPartClientFidel');

        // Revenus par compétences
        $chiffreAffairesCompetencesMandat = new Indicateur();
        $chiffreAffairesCompetencesMandat->setTitre('CA par compétences')
            ->setMethode('getCACompetences');

        $chiffreAffaires->setCategorie('Treso');
        $chiffreAffairesMandat->setCategorie('Treso');
        $sortieNFFA->setCategorie('Treso');
        $repartitionSortieNFFA->setCategorie('Treso');
        $ressourcesHumaines->setCategorie('Gestion');
        $membresParPromo->setCategorie('Gestion');
        $membres->setCategorie('Gestion');
        $repartitionClient->setCategorie('Com');
        $repartitionCAClient->setCategorie('Com');
        $repartitionSourceProspectionClient->setCategorie('Com');
        $repartitionSourceProspectionCAClient->setCategorie('Com');
        $chiffreAffairesCompetencesMandat->setCategorie('Com');
        $clientFidel->setCategorie('Com');
        $tauxAvenant->setCategorie('Suivi');
        $nombreEtudes->setCategorie('Suivi');
        $retardSurEtude->setCategorie('Suivi');
        $nombreDeFormationsParMandat->setCategorie('Rfp');
        $presenceAuxFormationsTimed->setCategorie('Rfp');

        $manager->persist($chiffreAffaires);
        $manager->persist($chiffreAffairesMandat);
        $manager->persist($sortieNFFA);
        $manager->persist($repartitionSortieNFFA);
        $manager->persist($ressourcesHumaines);
        $manager->persist($membresParPromo);
        $manager->persist($membres);
        $manager->persist($repartitionClient);
        $manager->persist($repartitionCAClient);
        $manager->persist($repartitionSourceProspectionClient);
        $manager->persist($repartitionSourceProspectionCAClient);
        $manager->persist($clientFidel);
        $manager->persist($chiffreAffairesCompetencesMandat);
        $manager->persist($tauxAvenant);
        $manager->persist($nombreEtudes);
        $manager->persist($retardSurEtude);
        $manager->persist($nombreDeFormationsParMandat);
        $manager->persist($presenceAuxFormationsTimed);

        $manager->flush();
    }
}
