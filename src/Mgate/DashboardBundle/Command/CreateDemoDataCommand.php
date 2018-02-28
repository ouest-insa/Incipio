<?php

namespace Mgate\DashboardBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Mgate\PersonneBundle\Entity\Employe;
use Mgate\PersonneBundle\Entity\Filiere;
use Mgate\PersonneBundle\Entity\Mandat;
use Mgate\PersonneBundle\Entity\Membre;
use Mgate\PersonneBundle\Entity\Personne;
use Mgate\PersonneBundle\Entity\Prospect;
use Mgate\SuiviBundle\Entity\Ap;
use Mgate\SuiviBundle\Entity\Cc;
use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Entity\GroupePhases;
use Mgate\SuiviBundle\Entity\Mission;
use Mgate\SuiviBundle\Entity\Phase;
use Mgate\SuiviBundle\Entity\ProcesVerbal;
use Mgate\TresoBundle\Entity\Facture;
use Mgate\TresoBundle\Entity\FactureDetail;
use N7consulting\RhBundle\Entity\Competence;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateDemoDataCommand extends ContainerAwareCommand
{
    const NOM = ['Henry', 'Martinez', 'Durand', 'Duval', 'Leroux', 'Robert', 'Morel', 'Bourgeois', 'Dupont', 'Dumont', 'Bernard', 'Francois', 'Dupond', 'Dubois', 'Blanc', 'Paul', 'Petit'];

    const PRENOM = ['Alexandre', 'Paul', 'Thomas', 'Raphaël', 'Camille', 'Inès', 'Emma', 'Gabriel', 'Antoine', 'Louis', 'Victor', 'Maxime', 'Hugo', 'Louise', 'Marie', 'Sarah', 'Arthur', 'Clara', 'Lea', 'Alice', 'Lucas', 'Jules', 'Chloe', 'Elsa', 'Manon'];

    const FILIERES = ['Hydro', 'Electronique', 'Telecoms', 'Automatique', 'Info'];

    const ETUDES = [
        [
            'nom' => '315GLA',
            'description' => 'Realisation site web',
            'statut' => Etude::ETUDE_STATE_NEGOCIATION,
            'nbrJEH' => 9,
            'duree' => 5,
            'dateCC' => 'ok',
            'prospect' => ['entreprise' => 'Gladiator Consulting',
                'adresse' => '3 rue du chene noir',
                'codePostal' => 33100,
                'ville' => 'Toulouse',
                'entite' => 2,
                'email' => 'contact@glad.fr',
            ],
        ],
        [
            'nom' => '316BLA',
            'description' => 'Electronique avancee',
            'statut' => Etude::ETUDE_STATE_COURS,
            'nbrJEH' => 5,
            'duree' => 3,
            'dateCC' => 'ok',
            'prospect' => ['entreprise' => 'Blackwater',
                'adresse' => '1020 5th Avenue',
                'codePostal' => 92200,
                'ville' => 'Neuilly',
                'entite' => 3,
                'email' => 'hello@black.ninja',
            ],
        ],
        [
            'nom' => '317IMU',
            'description' => 'Design Base de donnes',
            'statut' => Etude::ETUDE_STATE_CLOTUREE,
            'nbrJEH' => 8,
            'duree' => 4,
            'dateCC' => 'ok',
            'prospect' => ['entreprise' => 'Imuka',
                'adresse' => 'Kuruma San',
                'codePostal' => 91000,
                'ville' => 'Evry',
                'entite' => 4,
                'email' => 'contact@imuka.jp',
            ],
        ],
        [
            'nom' => '319UNI',
            'description' => 'Conception Radar recul',
            'statut' => Etude::ETUDE_STATE_CLOTUREE,
            'nbrJEH' => 12,
            'duree' => 8,
            'dateCC' => 'ok',
            'prospect' => ['entreprise' => 'Universal rad',
                'adresse' => '2 rue Marie Curie',
                'codePostal' => 35000,
                'ville' => 'Rennes',
                'entite' => 5,
                'email' => 'contact@univ.radar',
            ],
        ],
        [
            'nom' => '320TEK',
            'description' => 'Refactorisation code Java',
            'statut' => Etude::ETUDE_STATE_COURS,
            'nbrJEH' => 10,
            'duree' => 8,
            'dateCC' => 'ok',
            'prospect' => ['entreprise' => 'Teknik studio',
                'adresse' => '10 impasse sunderland',
                'codePostal' => 35000,
                'ville' => 'Rennes',
                'entite' => 6,
                'email' => 'contact@teknik.paris',
            ],
        ],
        [
            'nom' => '321DUV',
            'description' => 'Calcul de flux thermique',
            'statut' => Etude::ETUDE_STATE_COURS,
            'nbrJEH' => 9,
            'duree' => 4,
            'dateCC' => 'ok',
            'prospect' => ['entreprise' => 'Duvilcolor',
                'adresse' => '600 la pyrennene ',
                'codePostal' => 33100,
                'ville' => 'Labege',
                'entite' => 4,
                'email' => 'contact@duvilcol.or',
            ],
        ],
        [
            'nom' => '322NIL',
            'description' => 'Application Android',
            'statut' => Etude::ETUDE_STATE_NEGOCIATION,
            'nbrJEH' => 8,
            'duree' => 12,
            'dateCC' => 'ok',
            'prospect' => ['entreprise' => 'Nilsen Industries',
                'adresse' => '2 rue saint-louis',
                'codePostal' => 31000,
                'ville' => 'Bordeaux',
                'entite' => 7,
                'email' => 'contact@nislen.com',
            ],
        ],
        [
            'nom' => '323PRR',
            'description' => 'Etude de faisabilite',
            'statut' => Etude::ETUDE_STATE_PAUSE,
            'nbrJEH' => 4,
            'duree' => 4,
            'dateCC' => 'ok',
            'prospect' => ['entreprise' => 'PRR',
                'adresse' => 'PRR',
                'codePostal' => 35000,
                'ville' => 'Rennes',
                'entite' => 4,
                'email' => 'contact@prr.cn',
            ],
        ],
    ];

    /** @var ObjectManager */
    private $em;

    /** @var ValidatorInterface */
    private $validator;

    /** @var Membre[] */
    private $membres = [];

    private $etudes = [];

    /** @var Membre */
    private $president;

    /** @var Membre */
    private $vp;

    /** @var Filiere[] */
    private $filieres = [];

    /** @var Competence[] */
    private $competences = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('demo:create_data')
            ->setDescription('Create some demonstration data')
            ->setHelp('Creates some fake data for every module in order to have a nice overview of all functionnality.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->validator = $this->getContainer()->get('validator');
        $this->competences = $this->em->getRepository('N7consultingRhBundle:Competence')->findAll();

        $this->createFilieres($output);
        $this->createMembres($output);

        $this->createEtudes($output);

        //manage AP, CC & PVR
        $this->createDocuments($output);

        $output->writeln('Done.');
    }

    private function createFilieres(OutputInterface $output)
    {
        /* Filiere management */
        foreach (self::FILIERES as $ff) {
            $nf = new Filiere();
            $nf->setDescription('Demo filiere');
            $nf->setNom($ff);
            $this->validateObject('New filiere', $nf);
            $this->em->persist($nf);
            $this->filieres[] = $nf;
        }
        $this->em->flush();
        $output->writeln('Filiere: Ok');
    }

    private function createMembres(OutputInterface $output)
    {
        $mandat = date('Y') + 2;

        $vp = $this->createMembre(self::PRENOM[array_rand(self::PRENOM)], self::NOM[array_rand(self::NOM)], $mandat);
        $initial = substr($vp->getPersonne()->getPrenom(), 0, 1) . substr($vp->getPersonne()->getNom(), 0, 1);
        $vp->setIdentifiant(strtoupper($initial . '1'));
        $m1 = new Mandat();
        $m1->setDebutMandat(new \DateTime(($mandat - 2) . '-03-16'));
        $m1->setFinMandat(new \DateTime(($mandat - 1) . '-03-16'));
        $m1->setMembre($vp);
        $m1->setPoste($this->em->getRepository('MgatePersonneBundle:Poste')->findOneByIntitule('Vice-président'));
        $this->validateObject('Mandat VP', $m1);
        $this->em->persist($m1);
        $this->em->persist($vp);
        $this->vp = $vp;

        $president = $this->createMembre(self::PRENOM[array_rand(self::PRENOM)], self::NOM[array_rand(self::NOM)], $mandat);
        $initial = substr($president->getPersonne()->getPrenom(), 0, 1) . substr($president->getPersonne()->getNom(), 0, 1);
        $president->setIdentifiant(strtoupper($initial . '2'));
        $m2 = new Mandat();
        $m2->setDebutMandat(new \DateTime(($mandat - 2) . '-03-16'));
        $m2->setFinMandat(new \DateTime(($mandat - 1) . '-03-16'));
        $m2->setMembre($president);
        $m2->setPoste($this->em->getRepository('MgatePersonneBundle:Poste')->findOneByIntitule('Président'));
        $this->em->persist($president);
        $this->em->persist($m2);
        $this->president = $president;
        $this->em->flush();

        $output->writeln('President & VP: Ok');
    }

    private function createEtudes(OutputInterface $output)
    {
        foreach (self::ETUDES as $etude) {
            $e = new Etude();
            // hack with 317IMU, to have some decent stats on welcome page
            $mandat = ('317IMU' === $etude['nom'] ? date('Y') : rand(intval(date('Y')) - 3, intval(date('Y'))));
            $month = rand(1, 10);
            $day = rand(1, 30);
            $e->setMandat($mandat);
            $e->setNom($etude['nom']);
            $e->setDescription($etude['description']);
            $e->setDateCreation(new \DateTime($mandat . '-' . $month . '-' . $day));
            $e->setStateID($etude['statut']);
            $e->setAcompte(true);
            $e->setPourcentageAcompte(0.3);
            $e->setFraisDossier(90);
            $e->setPresentationProjet('Presentation ' . $etude['description']);
            $e->setDescriptionPrestation('Describe what we will do here');
            $e->setSourceDeProspection(rand(1, 10));
            $this->validateObject('New Etude', $e);
            $this->em->persist($e);
            $c = $this->competences[array_rand($this->competences)];
            if (null !== $c) {
                $c->addEtude($e);
            }

            /* Prospect management */
            $p = new Prospect();
            $p->setNom($etude['prospect']['entreprise']);
            $p->setAdresse($etude['prospect']['adresse']);
            $p->setCodePostal($etude['prospect']['codePostal']);
            $p->setVille($etude['prospect']['ville']);
            $p->setEntite($etude['prospect']['entite']);

            $pe = new Personne();
            $pe->setPrenom(self::PRENOM[array_rand(self::PRENOM)]); //whitespace explode : not perfect but better than nothing
            $pe->setNom(self::NOM[array_rand(self::NOM)]);
            $pe->setEmailEstValide(true);
            $pe->setEstAbonneNewsletter(false);
            $pe->setEmail($etude['prospect']['email']);
            $pe->setAdresse($etude['prospect']['adresse']);
            $pe->setCodePostal($etude['prospect']['codePostal']);
            $pe->setVille($etude['prospect']['ville']);

            $emp = new Employe();
            $emp->setProspect($p);
            $p->addEmploye($emp);
            $emp->setPersonne($pe);
            $this->em->persist($emp->getPersonne());
            $this->em->persist($emp);
            $this->em->persist($p);
            $e->setProspect($p);

            //create phases
            $g = new GroupePhases(); //default group
            $g->setTitre('Random generated' . rand());
            $g->setNumero(1);
            $g->setDescription('Automatic description');
            $g->setEtude($e);
            $this->validateObject('New GroupePhases', $g);
            $this->em->persist($g);

            $k = rand(1, 3);
            for ($i = 0; $i < $k; ++$i) {
                $ph = new Phase();
                $ph->setEtude($e);
                $ph->setGroupe($g);
                $ph->setPosition($i);
                $ph->setNbrJEH(intval($etude['nbrJEH'] / $k));
                $ph->setPrixJEH(340);
                $ph->setTitre('phase ' . $i);
                $ph->setDelai(intval(($etude['duree'] * 7) / $k) - $i);
                $ph->setDateDebut(new \DateTime($mandat . '-' . $month . '-' . $day));
                $this->validateObject('New Phase ' . $i, $ph);
                $this->em->persist($ph);
            }

            //manage project manager
            $pm = $this->createMembre(self::PRENOM[array_rand(self::PRENOM)], self::NOM[array_rand(self::NOM)], $mandat + 2);
            $this->em->persist($pm);
            if (null !== $c && !$c->getMembres()->contains($pm)) {
                $c->addMembre($pm);
            }
            $e->setSuiveur($pm->getPersonne());
            $e->setSuiveurQualite($this->membres[array_rand($this->membres)]->getPersonne());

            //manage intervenant
            if ($etude['statut'] > Etude::ETUDE_STATE_NEGOCIATION && $etude['statut'] < Etude::ETUDE_STATE_AVORTEE) {
                //manage developper
                $mdev = $this->createMembre(self::PRENOM[array_rand(self::PRENOM)], self::NOM[array_rand(self::NOM)], $mandat + 1);
                $this->em->persist($mdev);
                if (null !== $c && !$c->getMembres()->contains($mdev)) {
                    $c->addMembre($mdev);
                }

                $mi = new Mission();
                $mi->setSignataire2($mdev->getPersonne());
                $mi->setSignataire1($this->president->getPersonne());
                $mi->setEtude($e);
                $mi->setDateSignature(new \DateTime($mandat . '-' . $month . '-' . $day));
                $mi->setDebutOm(new \DateTime($mandat . '-' . $month . '-' . $day));
                $mi->setFinOm(new \DateTime($mandat . '-' . ($month + 1) . '-' . $day));
                $mi->setAvancement(rand(10, 95));
                $mi->setIntervenant($mdev);
                $this->validateObject('New Mission', $mi);
                $this->em->persist($mi);
            }

            $this->etudes[$etude['nom']] = $e;
        }

        $this->em->flush();
        $output->writeln('Etudes: Ok');
    }

    private function createDocuments(OutputInterface $output)
    {
        /** @var Etude $etude */
        foreach ($this->etudes as $key => $etude) {
            if ($etude->getStateID() > Etude::ETUDE_STATE_NEGOCIATION) {
                $ap = new Ap();
                $ap->setEtude($etude);
                $etude->setAp($ap);
                $ap->setDateSignature($etude->getDateCreation());
                $ap->setSignataire1($this->president->getPersonne());
                $ap->setContactMgate($this->vp->getPersonne());
                /** @var Employe $emp */
                $emp = $etude->getProspect()->getEmployes()[0];
                $ap->setSignataire2(null !== $emp ? $emp->getPersonne() : null);
                $ap->setNbrDev(rand(1, 2));
                $this->validateObject('New AP', $ap);
                $this->em->persist($ap);

                $cc = new Cc();
                $cc->setDateSignature($etude->getDateCreation());
                $cc->setSignataire1($this->president->getPersonne());
                $cc->setSignataire2(null !== $emp ? $emp->getPersonne() : null);
                $etude->setCc($cc);
                $this->validateObject('New CC', $cc);
                $this->em->persist($cc);

                if ($etude->getStateID() > Etude::ETUDE_STATE_NEGOCIATION && $etude->getStateID() < Etude::ETUDE_STATE_AVORTEE) {
                    $pv = new ProcesVerbal();
                    $pv->setEtude($etude);
                    $endDate = clone $etude->getDateCreation();
                    $pv->setDateSignature($endDate->modify('+1 month'));
                    $pv->setSignataire1($this->president->getPersonne());
                    $pv->setSignataire2(null !== $emp ? $emp->getPersonne() : null);
                    $pv->setType('pvr');
                    $this->validateObject('New PVR', $pv);
                    $this->em->persist($pv);
                }

                if (Etude::ETUDE_STATE_CLOTUREE == $etude->getStateID()) {
                    $compteAcompte = 419100;

                    $fa = new Facture();
                    $fa->setType(Facture::TYPE_VENTE_ACCOMPTE);
                    $fa->setObjet('Facture d\'acompte sur l\'étude ' . $etude->getReference('nom') . ', correspondant au règlement de ' . (($etude->getPourcentageAcompte() * 100)) . ' % de l’étude.');
                    $fa->setExercice($etude->getDateCreation()->format('Y'));
                    $fa->setNumero(1);
                    $fa->setEtude($etude);
                    $fa->setBeneficiaire($etude->getProspect());
                    $endDate = clone $etude->getDateCreation();
                    $fa->setDateEmission($endDate->modify('+1 month'));

                    $detail = new FactureDetail();
                    $detail->setCompte($this->em->getRepository('MgateTresoBundle:Compte')->findOneBy(['numero' => $compteAcompte]));
                    $detail->setFacture($fa);
                    $fa->addDetail($detail);
                    $detail->setDescription('Acompte de ' . ($etude->getPourcentageAcompte() * 100) . ' % sur l\'étude ' . $etude->getReference());
                    $detail->setMontantHT($etude->getPourcentageAcompte() * $etude->getMontantHT());
                    $detail->setTauxTVA(20);
                    $this->validateObject('new FA', $fa);
                    $this->em->persist($fa);
                }
            }
        }
        $this->em->flush();
        $output->writeln('Documents: Ok');
    }

    /**
     * @param $prenom
     * @param $nom
     * @param $promotion
     *
     * @return Membre
     */
    private function createMembre($prenom, $nom, $promotion)
    {
        $vp = new Personne();
        $vp->setPrenom($prenom);
        $vp->setNom($nom);
        $vp->setEmail($prenom . '' . $nom . '@localhost.localdomain');
        $vp->setMobile('0' . rand(111111111, 999999999));
        $vp->setEmailEstValide(false);
        $vp->setEstAbonneNewsletter(false);
        $this->validateObject('New vp Personne', $vp);
        $this->em->persist($vp);

        $mvp = new Membre();
        $mvp->setPersonne($vp);
        $mvp->setPromotion($promotion);
        $mvp->setEmailEMSE(substr($prenom, 0, 1) . '' . $nom . '@etu.localdomain');
        $mvp->setFiliere($this->filieres[array_rand($this->filieres)]);
        $this->validateObject('New vp Membre', $mvp);
        $this->em->persist($mvp);
        $c = $this->competences[array_rand($this->competences)];
        $c->addMembre($mvp);
        $this->membres[] = $mvp;

        return $mvp;
    }

    private function validateObject(string $point, $object)
    {
        $constraints = $this->validator->validate($object);
        if (0 !== count($constraints)) {
            $message = 'At ' . $point;
            /** @var ConstraintViolationInterface $cs */
            foreach ($constraints as $cs) {
                $message .= ' * ' . $cs->getPropertyPath() . ' ' . $cs->getMessage();
            }

            throw new \Exception($message);
        }
    }
}
