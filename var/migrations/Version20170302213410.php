<?php

namespace Application\Migrations;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration 0: Create the schema
 */
class Version20170302213410 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /**
         * Jeyser got migrations quite late in the project. And at the beginning, they were used as a way to execute
         * SQL and were not thought to be used to manage the whole schema.
         * This migration has been added much later after the first migration. Therefore, we have to ensure that every
         * deployed version at the time of the update will behave well.
         *
         * This migration is supposed to create the schema. Thus we check if table fos_user is available or not.
         * If it is, we are running the migration on an already created database, then we return because those tables are
         *      already in place.
         * If it isn't, the database hasn't been created, so let's run this migration
         */
        try {
            $this->connection->executeQuery('SELECT 1 FROM fos_user LIMIT 1;')->execute();
            return;
        } catch (TableNotFoundException $e) {
            // table not available, do nothing, just keep on and create the tables.
        }

        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(255) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, personne_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_957A6479A21BD112 (personne_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Document (id INT AUTO_INCREMENT NOT NULL, relation_id INT DEFAULT NULL, author_personne_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, size INT NOT NULL, uptime DATETIME NOT NULL, path VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_211FE8203256915B (relation_id), INDEX IDX_211FE820FEEAB26A (author_personne_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE RelatedDocument (id INT AUTO_INCREMENT NOT NULL, document_id INT DEFAULT NULL, membre_id INT DEFAULT NULL, etude_id INT DEFAULT NULL, formation_id INT DEFAULT NULL, prospect_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_E28BFD66C33F7837 (document_id), INDEX IDX_E28BFD666A99F74A (membre_id), INDEX IDX_E28BFD6647ABD362 (etude_id), INDEX IDX_E28BFD665200282E (formation_id), INDEX IDX_E28BFD66D182060A (prospect_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE AdminParam (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(31) NOT NULL, paramType VARCHAR(31) NOT NULL, defaultValue VARCHAR(255) NOT NULL, required TINYINT(1) NOT NULL, paramLabel VARCHAR(63) NOT NULL, paramDescription VARCHAR(255) DEFAULT NULL, priority INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Indicateur (id INT AUTO_INCREMENT NOT NULL, categorie VARCHAR(15) NOT NULL, titre VARCHAR(255) NOT NULL, methode VARCHAR(127) NOT NULL, options TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Facture (id INT AUTO_INCREMENT NOT NULL, etude_id INT DEFAULT NULL, beneficiaire_id INT NOT NULL, exercice SMALLINT NOT NULL, numero SMALLINT NOT NULL, type SMALLINT NOT NULL, dateEmission DATE NOT NULL, dateVersement DATE DEFAULT NULL, objet LONGTEXT NOT NULL, montantADeduire_id INT DEFAULT NULL, INDEX IDX_313B5D8C47ABD362 (etude_id), INDEX IDX_313B5D8C5AF81F68 (beneficiaire_id), UNIQUE INDEX UNIQ_313B5D8CD4F76809 (montantADeduire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE FactureDetail (id INT AUTO_INCREMENT NOT NULL, facture_id INT DEFAULT NULL, compte_id INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, montantHT NUMERIC(6, 2) DEFAULT NULL, tauxTVA NUMERIC(6, 2) DEFAULT NULL, INDEX IDX_82D8557B7F2DEE08 (facture_id), INDEX IDX_82D8557BF2C56620 (compte_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CotisationURSSAF (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, isSurBaseURSSAF TINYINT(1) NOT NULL, tauxPartJE NUMERIC(10, 5) NOT NULL, tauxPartEtu NUMERIC(10, 5) NOT NULL, dateDebut DATE NOT NULL, dateFin DATE NOT NULL, deductible TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE BV (id INT AUTO_INCREMENT NOT NULL, mission_id INT DEFAULT NULL, mandat SMALLINT NOT NULL, numero SMALLINT NOT NULL, nombreJEH SMALLINT NOT NULL, remunerationBruteParJEH DOUBLE PRECISION NOT NULL, dateDeVersement DATE NOT NULL, dateDemission DATE NOT NULL, typeDeTravail VARCHAR(255) NOT NULL, numeroVirement VARCHAR(255) NOT NULL, baseURSSAF_id INT DEFAULT NULL, INDEX IDX_19ECBB9BE6CAE90 (mission_id), INDEX IDX_19ECBB9772FE1FD (baseURSSAF_id), UNIQUE INDEX UNIQ_19ECBB91E53EFD5F55AE19E (mandat, numero), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bv_cotisationurssaf (bv_id INT NOT NULL, cotisationurssaf_id INT NOT NULL, INDEX IDX_C27204B3F2843052 (bv_id), INDEX IDX_C27204B312CF54B5 (cotisationurssaf_id), PRIMARY KEY(bv_id, cotisationurssaf_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE NoteDeFrais (id INT AUTO_INCREMENT NOT NULL, demandeur_id INT NOT NULL, date DATE NOT NULL, mandat INT NOT NULL, numero INT NOT NULL, objet LONGTEXT NOT NULL, INDEX IDX_30CFBBFC95A6EE59 (demandeur_id), UNIQUE INDEX UNIQ_30CFBBFC1E53EFD5F55AE19E (mandat, numero), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Compte (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, numero VARCHAR(6) NOT NULL, categorie TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_C85A5756F55AE19E (numero), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE BaseURSSAF (id INT AUTO_INCREMENT NOT NULL, baseURSSAF NUMERIC(4, 2) NOT NULL, dateDebut DATE NOT NULL, dateFin DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE NoteDeFraisDetail (id INT AUTO_INCREMENT NOT NULL, compte_id INT DEFAULT NULL, description LONGTEXT NOT NULL, prixHT NUMERIC(6, 2) DEFAULT NULL, tauxTVA NUMERIC(6, 2) DEFAULT NULL, type SMALLINT NOT NULL, kilometrage INT DEFAULT NULL, tauxKm INT DEFAULT NULL, noteDeFrais_id INT NOT NULL, INDEX IDX_26A881021B119F3E (noteDeFrais_id), INDEX IDX_26A88102F2C56620 (compte_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Formation (id INT AUTO_INCREMENT NOT NULL, mandat INT NOT NULL, categorie LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, dateDebut DATETIME NOT NULL, dateFin DATETIME NOT NULL, doc VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation_formateurs (formation_id INT NOT NULL, personne_id INT NOT NULL, INDEX IDX_528364D5200282E (formation_id), INDEX IDX_528364DA21BD112 (personne_id), PRIMARY KEY(formation_id, personne_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation_membresPresents (formation_id INT NOT NULL, personne_id INT NOT NULL, INDEX IDX_232FA65D5200282E (formation_id), INDEX IDX_232FA65DA21BD112 (personne_id), PRIMARY KEY(formation_id, personne_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Employe (id INT AUTO_INCREMENT NOT NULL, prospect_id INT NOT NULL, personne_id INT NOT NULL, poste VARCHAR(255) DEFAULT NULL, INDEX IDX_37B9EA25D182060A (prospect_id), UNIQUE INDEX UNIQ_37B9EA25A21BD112 (personne_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Membre (id INT AUTO_INCREMENT NOT NULL, personne_id INT DEFAULT NULL, filiere_id INT DEFAULT NULL, dateCE DATE DEFAULT NULL, identifiant VARCHAR(10) DEFAULT NULL, emailEMSE VARCHAR(50) DEFAULT NULL, promotion SMALLINT DEFAULT NULL, birthdate DATE DEFAULT NULL, placeofbirth VARCHAR(255) DEFAULT NULL, nationalite VARCHAR(255) DEFAULT NULL, photoURI VARCHAR(255) DEFAULT NULL, formatPaiement VARCHAR(15) DEFAULT NULL, securiteSociale VARCHAR(25) DEFAULT NULL, UNIQUE INDEX UNIQ_F118FE1FC90409EC (identifiant), UNIQUE INDEX UNIQ_F118FE1FA21BD112 (personne_id), INDEX IDX_F118FE1F180AA129 (filiere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Mandat (id INT AUTO_INCREMENT NOT NULL, membre_id INT DEFAULT NULL, poste_id INT DEFAULT NULL, debutMandat DATE NOT NULL, finMandat DATE NOT NULL, INDEX IDX_19FFEAE36A99F74A (membre_id), INDEX IDX_19FFEAE3A0905086 (poste_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Poste (id INT AUTO_INCREMENT NOT NULL, intitule VARCHAR(127) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Filiere (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(63) NOT NULL, description VARCHAR(127) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Personne (id INT AUTO_INCREMENT NOT NULL, employe_id INT DEFAULT NULL, user_id INT DEFAULT NULL, membre_id INT DEFAULT NULL, adresse VARCHAR(127) DEFAULT NULL, codepostal INT DEFAULT NULL, ville VARCHAR(63) DEFAULT NULL, pays VARCHAR(63) DEFAULT NULL, prenom VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, sexe VARCHAR(15) DEFAULT NULL, mobile VARCHAR(31) DEFAULT NULL, fix VARCHAR(31) DEFAULT NULL, email VARCHAR(63) DEFAULT NULL, emailestvalide TINYINT(1) DEFAULT \'1\' NOT NULL, estabonnenewsletter TINYINT(1) DEFAULT \'1\' NOT NULL, UNIQUE INDEX UNIQ_F6B8ABB91B65292 (employe_id), UNIQUE INDEX UNIQ_F6B8ABB9A76ED395 (user_id), UNIQUE INDEX UNIQ_F6B8ABB96A99F74A (membre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Prospect (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, adresse VARCHAR(127) DEFAULT NULL, codepostal INT DEFAULT NULL, ville VARCHAR(63) DEFAULT NULL, pays VARCHAR(63) DEFAULT NULL, nom VARCHAR(63) NOT NULL, entite INT DEFAULT NULL, UNIQUE INDEX UNIQ_30B8EE2BE2904019 (thread_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Thread (id VARCHAR(255) NOT NULL, permalink VARCHAR(255) NOT NULL, is_commentable TINYINT(1) NOT NULL, num_comments INT NOT NULL, last_comment_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Comment (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, author_id INT DEFAULT NULL, body LONGTEXT NOT NULL, ancestors VARCHAR(1024) NOT NULL, depth INT NOT NULL, created_at DATETIME NOT NULL, state INT NOT NULL, INDEX IDX_5BC96BF0E2904019 (thread_id), INDEX IDX_5BC96BF0F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Suivi (id INT AUTO_INCREMENT NOT NULL, etude_id INT NOT NULL, date DATE NOT NULL, etat LONGTEXT NOT NULL, todo LONGTEXT NOT NULL, INDEX IDX_EF7DE58B47ABD362 (etude_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Av (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, signataire1_id INT DEFAULT NULL, signataire2_id INT DEFAULT NULL, etude_id INT NOT NULL, version INT DEFAULT NULL, redige TINYINT(1) DEFAULT NULL, relu TINYINT(1) DEFAULT NULL, spt1 TINYINT(1) DEFAULT NULL, spt2 TINYINT(1) DEFAULT NULL, dateSignature DATETIME DEFAULT NULL, envoye TINYINT(1) DEFAULT NULL, receptionne TINYINT(1) DEFAULT NULL, generer INT DEFAULT NULL, differentielDelai INT DEFAULT 0 NOT NULL, objet LONGTEXT NOT NULL, clauses LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_11DDB8B2E2904019 (thread_id), INDEX IDX_11DDB8B2C71184C3 (signataire1_id), INDEX IDX_11DDB8B2D5A42B2D (signataire2_id), INDEX IDX_11DDB8B247ABD362 (etude_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Ap (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, signataire1_id INT DEFAULT NULL, signataire2_id INT DEFAULT NULL, etude_id INT NOT NULL, version INT DEFAULT NULL, redige TINYINT(1) DEFAULT NULL, relu TINYINT(1) DEFAULT NULL, spt1 TINYINT(1) DEFAULT NULL, spt2 TINYINT(1) DEFAULT NULL, dateSignature DATETIME DEFAULT NULL, envoye TINYINT(1) DEFAULT NULL, receptionne TINYINT(1) DEFAULT NULL, generer INT DEFAULT NULL, nbrDev INT DEFAULT NULL, deonto TINYINT(1) DEFAULT NULL, contactMgate_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_F8BE1D87E2904019 (thread_id), INDEX IDX_F8BE1D87C71184C3 (signataire1_id), INDEX IDX_F8BE1D87D5A42B2D (signataire2_id), UNIQUE INDEX UNIQ_F8BE1D8747ABD362 (etude_id), INDEX IDX_F8BE1D872DCE3B21 (contactMgate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE GroupePhases (id INT AUTO_INCREMENT NOT NULL, etude_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, numero SMALLINT NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_D70195E747ABD362 (etude_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Mission (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, signataire1_id INT DEFAULT NULL, signataire2_id INT DEFAULT NULL, etude_id INT NOT NULL, intervenant_id INT DEFAULT NULL, version INT DEFAULT NULL, redige TINYINT(1) DEFAULT NULL, relu TINYINT(1) DEFAULT NULL, spt1 TINYINT(1) DEFAULT NULL, spt2 TINYINT(1) DEFAULT NULL, dateSignature DATETIME DEFAULT NULL, envoye TINYINT(1) DEFAULT NULL, receptionne TINYINT(1) DEFAULT NULL, generer INT DEFAULT NULL, debutOm DATETIME DEFAULT NULL, finOm DATETIME DEFAULT NULL, pourcentageJunior DOUBLE PRECISION NOT NULL, avancement INT DEFAULT NULL, rapportDemande TINYINT(1) DEFAULT NULL, rapportRelu TINYINT(1) DEFAULT NULL, remunere TINYINT(1) DEFAULT NULL, referentTechnique_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_5FDACBA0E2904019 (thread_id), INDEX IDX_5FDACBA0C71184C3 (signataire1_id), INDEX IDX_5FDACBA0D5A42B2D (signataire2_id), INDEX IDX_5FDACBA047ABD362 (etude_id), INDEX IDX_5FDACBA0593919F0 (referentTechnique_id), INDEX IDX_5FDACBA0AB9A1716 (intervenant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Etude (id INT AUTO_INCREMENT NOT NULL, prospect_id INT NOT NULL, suiveur_id INT DEFAULT NULL, thread_id VARCHAR(255) DEFAULT NULL, mandat INT NOT NULL, num INT DEFAULT NULL, nom VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, dateCreation DATETIME NOT NULL, dateModification DATETIME NOT NULL, stateID INT NOT NULL, stateDescription LONGTEXT DEFAULT NULL, confidentiel TINYINT(1) DEFAULT NULL, auditDate DATE DEFAULT NULL, auditType INT DEFAULT NULL, acompte TINYINT(1) DEFAULT NULL, pourcentageAcompte NUMERIC(10, 2) DEFAULT NULL, fraisDossier INT DEFAULT NULL, presentationProjet LONGTEXT DEFAULT NULL, descriptionPrestation LONGTEXT DEFAULT NULL, prestation INT DEFAULT NULL, sourceDeProspection INT DEFAULT NULL, domaineCompetence_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_DC1F8620DC43AF6E (num), UNIQUE INDEX UNIQ_DC1F86206C6E55B5 (nom), INDEX IDX_DC1F8620D182060A (prospect_id), INDEX IDX_DC1F862035E10B95 (suiveur_id), UNIQUE INDEX UNIQ_DC1F8620E2904019 (thread_id), INDEX IDX_DC1F86204C79389A (domaineCompetence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Cc (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, signataire1_id INT DEFAULT NULL, signataire2_id INT DEFAULT NULL, etude_id INT NOT NULL, version INT DEFAULT NULL, redige TINYINT(1) DEFAULT NULL, relu TINYINT(1) DEFAULT NULL, spt1 TINYINT(1) DEFAULT NULL, spt2 TINYINT(1) DEFAULT NULL, dateSignature DATETIME DEFAULT NULL, envoye TINYINT(1) DEFAULT NULL, receptionne TINYINT(1) DEFAULT NULL, generer INT DEFAULT NULL, UNIQUE INDEX UNIQ_4E363EDBE2904019 (thread_id), INDEX IDX_4E363EDBC71184C3 (signataire1_id), INDEX IDX_4E363EDBD5A42B2D (signataire2_id), UNIQUE INDEX UNIQ_4E363EDB47ABD362 (etude_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ClientContact (id INT AUTO_INCREMENT NOT NULL, etude_id INT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, objet LONGTEXT DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, moyenContact LONGTEXT DEFAULT NULL, faitPar_id INT NOT NULL, INDEX IDX_9C34386147ABD362 (etude_id), INDEX IDX_9C3438615302E431 (faitPar_id), UNIQUE INDEX UNIQ_9C343861E2904019 (thread_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ProcesVerbal (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, signataire1_id INT DEFAULT NULL, signataire2_id INT DEFAULT NULL, etude_id INT DEFAULT NULL, version INT DEFAULT NULL, redige TINYINT(1) DEFAULT NULL, relu TINYINT(1) DEFAULT NULL, spt1 TINYINT(1) DEFAULT NULL, spt2 TINYINT(1) DEFAULT NULL, dateSignature DATETIME DEFAULT NULL, envoye TINYINT(1) DEFAULT NULL, receptionne TINYINT(1) DEFAULT NULL, generer INT DEFAULT NULL, phaseIDs INT DEFAULT NULL, type LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_D8EBE2BFE2904019 (thread_id), INDEX IDX_D8EBE2BFC71184C3 (signataire1_id), INDEX IDX_D8EBE2BFD5A42B2D (signataire2_id), INDEX IDX_D8EBE2BF47ABD362 (etude_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Phase (id INT AUTO_INCREMENT NOT NULL, etude_id INT DEFAULT NULL, groupe_id INT DEFAULT NULL, avenant_id INT DEFAULT NULL, mission_id INT DEFAULT NULL, position INT DEFAULT NULL, nbrJEH INT DEFAULT NULL, prixJEH INT DEFAULT NULL, titre LONGTEXT DEFAULT NULL, objectif LONGTEXT DEFAULT NULL, methodo LONGTEXT DEFAULT NULL, dateDebut DATETIME DEFAULT NULL, delai INT DEFAULT NULL, validation INT DEFAULT NULL, etatSurAvenant INT DEFAULT NULL, INDEX IDX_707CF9CF47ABD362 (etude_id), INDEX IDX_707CF9CF7A45358C (groupe_id), INDEX IDX_707CF9CF85631A3A (avenant_id), INDEX IDX_707CF9CFBE6CAE90 (mission_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE AvMission (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, signataire1_id INT DEFAULT NULL, signataire2_id INT DEFAULT NULL, etude_id INT DEFAULT NULL, mission_id INT DEFAULT NULL, avenant_id INT DEFAULT NULL, version INT DEFAULT NULL, redige TINYINT(1) DEFAULT NULL, relu TINYINT(1) DEFAULT NULL, spt1 TINYINT(1) DEFAULT NULL, spt2 TINYINT(1) DEFAULT NULL, dateSignature DATETIME DEFAULT NULL, envoye TINYINT(1) DEFAULT NULL, receptionne TINYINT(1) DEFAULT NULL, generer INT DEFAULT NULL, nouveauPourcentage INT NOT NULL, differentielDelai INT NOT NULL, UNIQUE INDEX UNIQ_87698F23E2904019 (thread_id), INDEX IDX_87698F23C71184C3 (signataire1_id), INDEX IDX_87698F23D5A42B2D (signataire2_id), INDEX IDX_87698F2347ABD362 (etude_id), INDEX IDX_87698F23BE6CAE90 (mission_id), INDEX IDX_87698F2385631A3A (avenant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE RepartitionJEH (id INT AUTO_INCREMENT NOT NULL, mission_id INT DEFAULT NULL, nombreJEH INT DEFAULT NULL, prixJEH INT DEFAULT NULL, avMission_id INT DEFAULT NULL, INDEX IDX_5E061BA8BE6CAE90 (mission_id), INDEX IDX_5E061BA8CC0EBB8E (avMission_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE DomaineCompetence (id INT AUTO_INCREMENT NOT NULL, nom LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Competence (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(50) DEFAULT NULL, nom VARCHAR(20) NOT NULL, UNIQUE INDEX NameConstraintes (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competence_membre (competence_id INT NOT NULL, membre_id INT NOT NULL, INDEX IDX_BF579E3515761DAB (competence_id), INDEX IDX_BF579E356A99F74A (membre_id), PRIMARY KEY(competence_id, membre_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competence_etude (competence_id INT NOT NULL, etude_id INT NOT NULL, INDEX IDX_97FCF8C215761DAB (competence_id), INDEX IDX_97FCF8C247ABD362 (etude_id), PRIMARY KEY(competence_id, etude_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Appel (id INT AUTO_INCREMENT NOT NULL, suiveur_id INT NOT NULL, employe_id INT NOT NULL, prospect_id INT NOT NULL, noteAppel LONGTEXT NOT NULL, dateAppel DATE NOT NULL, aRappeller TINYINT(1) DEFAULT \'1\' NOT NULL, dateRappel DATE DEFAULT NULL, INDEX IDX_C0F1FCB935E10B95 (suiveur_id), INDEX IDX_C0F1FCB91B65292 (employe_id), INDEX IDX_C0F1FCB9D182060A (prospect_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A6479A21BD112 FOREIGN KEY (personne_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Document ADD CONSTRAINT FK_211FE8203256915B FOREIGN KEY (relation_id) REFERENCES RelatedDocument (id)');
        $this->addSql('ALTER TABLE Document ADD CONSTRAINT FK_211FE820FEEAB26A FOREIGN KEY (author_personne_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE RelatedDocument ADD CONSTRAINT FK_E28BFD66C33F7837 FOREIGN KEY (document_id) REFERENCES Document (id)');
        $this->addSql('ALTER TABLE RelatedDocument ADD CONSTRAINT FK_E28BFD666A99F74A FOREIGN KEY (membre_id) REFERENCES Membre (id)');
        $this->addSql('ALTER TABLE RelatedDocument ADD CONSTRAINT FK_E28BFD6647ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE RelatedDocument ADD CONSTRAINT FK_E28BFD665200282E FOREIGN KEY (formation_id) REFERENCES Formation (id)');
        $this->addSql('ALTER TABLE RelatedDocument ADD CONSTRAINT FK_E28BFD66D182060A FOREIGN KEY (prospect_id) REFERENCES Prospect (id)');
        $this->addSql('ALTER TABLE Facture ADD CONSTRAINT FK_313B5D8C47ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE Facture ADD CONSTRAINT FK_313B5D8C5AF81F68 FOREIGN KEY (beneficiaire_id) REFERENCES Prospect (id)');
        $this->addSql('ALTER TABLE Facture ADD CONSTRAINT FK_313B5D8CD4F76809 FOREIGN KEY (montantADeduire_id) REFERENCES FactureDetail (id)');
        $this->addSql('ALTER TABLE FactureDetail ADD CONSTRAINT FK_82D8557B7F2DEE08 FOREIGN KEY (facture_id) REFERENCES Facture (id)');
        $this->addSql('ALTER TABLE FactureDetail ADD CONSTRAINT FK_82D8557BF2C56620 FOREIGN KEY (compte_id) REFERENCES Compte (id)');
        $this->addSql('ALTER TABLE BV ADD CONSTRAINT FK_19ECBB9BE6CAE90 FOREIGN KEY (mission_id) REFERENCES Mission (id)');
        $this->addSql('ALTER TABLE BV ADD CONSTRAINT FK_19ECBB9772FE1FD FOREIGN KEY (baseURSSAF_id) REFERENCES BaseURSSAF (id)');
        $this->addSql('ALTER TABLE bv_cotisationurssaf ADD CONSTRAINT FK_C27204B3F2843052 FOREIGN KEY (bv_id) REFERENCES BV (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bv_cotisationurssaf ADD CONSTRAINT FK_C27204B312CF54B5 FOREIGN KEY (cotisationurssaf_id) REFERENCES CotisationURSSAF (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE NoteDeFrais ADD CONSTRAINT FK_30CFBBFC95A6EE59 FOREIGN KEY (demandeur_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE NoteDeFraisDetail ADD CONSTRAINT FK_26A881021B119F3E FOREIGN KEY (noteDeFrais_id) REFERENCES NoteDeFrais (id)');
        $this->addSql('ALTER TABLE NoteDeFraisDetail ADD CONSTRAINT FK_26A88102F2C56620 FOREIGN KEY (compte_id) REFERENCES Compte (id)');
        $this->addSql('ALTER TABLE formation_formateurs ADD CONSTRAINT FK_528364D5200282E FOREIGN KEY (formation_id) REFERENCES Formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_formateurs ADD CONSTRAINT FK_528364DA21BD112 FOREIGN KEY (personne_id) REFERENCES Personne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_membresPresents ADD CONSTRAINT FK_232FA65D5200282E FOREIGN KEY (formation_id) REFERENCES Formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_membresPresents ADD CONSTRAINT FK_232FA65DA21BD112 FOREIGN KEY (personne_id) REFERENCES Personne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Employe ADD CONSTRAINT FK_37B9EA25D182060A FOREIGN KEY (prospect_id) REFERENCES Prospect (id)');
        $this->addSql('ALTER TABLE Employe ADD CONSTRAINT FK_37B9EA25A21BD112 FOREIGN KEY (personne_id) REFERENCES Personne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Membre ADD CONSTRAINT FK_F118FE1FA21BD112 FOREIGN KEY (personne_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Membre ADD CONSTRAINT FK_F118FE1F180AA129 FOREIGN KEY (filiere_id) REFERENCES Filiere (id)');
        $this->addSql('ALTER TABLE Mandat ADD CONSTRAINT FK_19FFEAE36A99F74A FOREIGN KEY (membre_id) REFERENCES Membre (id)');
        $this->addSql('ALTER TABLE Mandat ADD CONSTRAINT FK_19FFEAE3A0905086 FOREIGN KEY (poste_id) REFERENCES Poste (id)');
        $this->addSql('ALTER TABLE Personne ADD CONSTRAINT FK_F6B8ABB91B65292 FOREIGN KEY (employe_id) REFERENCES Employe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Personne ADD CONSTRAINT FK_F6B8ABB9A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Personne ADD CONSTRAINT FK_F6B8ABB96A99F74A FOREIGN KEY (membre_id) REFERENCES Membre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Prospect ADD CONSTRAINT FK_30B8EE2BE2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE Comment ADD CONSTRAINT FK_5BC96BF0E2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE Comment ADD CONSTRAINT FK_5BC96BF0F675F31B FOREIGN KEY (author_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE Suivi ADD CONSTRAINT FK_EF7DE58B47ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE Av ADD CONSTRAINT FK_11DDB8B2E2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE Av ADD CONSTRAINT FK_11DDB8B2C71184C3 FOREIGN KEY (signataire1_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Av ADD CONSTRAINT FK_11DDB8B2D5A42B2D FOREIGN KEY (signataire2_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Av ADD CONSTRAINT FK_11DDB8B247ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE Ap ADD CONSTRAINT FK_F8BE1D87E2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE Ap ADD CONSTRAINT FK_F8BE1D87C71184C3 FOREIGN KEY (signataire1_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Ap ADD CONSTRAINT FK_F8BE1D87D5A42B2D FOREIGN KEY (signataire2_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Ap ADD CONSTRAINT FK_F8BE1D8747ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Ap ADD CONSTRAINT FK_F8BE1D872DCE3B21 FOREIGN KEY (contactMgate_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE GroupePhases ADD CONSTRAINT FK_D70195E747ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE Mission ADD CONSTRAINT FK_5FDACBA0E2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE Mission ADD CONSTRAINT FK_5FDACBA0C71184C3 FOREIGN KEY (signataire1_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Mission ADD CONSTRAINT FK_5FDACBA0D5A42B2D FOREIGN KEY (signataire2_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Mission ADD CONSTRAINT FK_5FDACBA047ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE Mission ADD CONSTRAINT FK_5FDACBA0593919F0 FOREIGN KEY (referentTechnique_id) REFERENCES Membre (id)');
        $this->addSql('ALTER TABLE Mission ADD CONSTRAINT FK_5FDACBA0AB9A1716 FOREIGN KEY (intervenant_id) REFERENCES Membre (id)');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F8620D182060A FOREIGN KEY (prospect_id) REFERENCES Prospect (id)');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F862035E10B95 FOREIGN KEY (suiveur_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F8620E2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F86204C79389A FOREIGN KEY (domaineCompetence_id) REFERENCES DomaineCompetence (id)');
        $this->addSql('ALTER TABLE Cc ADD CONSTRAINT FK_4E363EDBE2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE Cc ADD CONSTRAINT FK_4E363EDBC71184C3 FOREIGN KEY (signataire1_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Cc ADD CONSTRAINT FK_4E363EDBD5A42B2D FOREIGN KEY (signataire2_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Cc ADD CONSTRAINT FK_4E363EDB47ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ClientContact ADD CONSTRAINT FK_9C34386147ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE ClientContact ADD CONSTRAINT FK_9C3438615302E431 FOREIGN KEY (faitPar_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE ClientContact ADD CONSTRAINT FK_9C343861E2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE ProcesVerbal ADD CONSTRAINT FK_D8EBE2BFE2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE ProcesVerbal ADD CONSTRAINT FK_D8EBE2BFC71184C3 FOREIGN KEY (signataire1_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE ProcesVerbal ADD CONSTRAINT FK_D8EBE2BFD5A42B2D FOREIGN KEY (signataire2_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE ProcesVerbal ADD CONSTRAINT FK_D8EBE2BF47ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE Phase ADD CONSTRAINT FK_707CF9CF47ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE Phase ADD CONSTRAINT FK_707CF9CF7A45358C FOREIGN KEY (groupe_id) REFERENCES GroupePhases (id)');
        $this->addSql('ALTER TABLE Phase ADD CONSTRAINT FK_707CF9CF85631A3A FOREIGN KEY (avenant_id) REFERENCES Av (id)');
        $this->addSql('ALTER TABLE Phase ADD CONSTRAINT FK_707CF9CFBE6CAE90 FOREIGN KEY (mission_id) REFERENCES Mission (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE AvMission ADD CONSTRAINT FK_87698F23E2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE AvMission ADD CONSTRAINT FK_87698F23C71184C3 FOREIGN KEY (signataire1_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE AvMission ADD CONSTRAINT FK_87698F23D5A42B2D FOREIGN KEY (signataire2_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE AvMission ADD CONSTRAINT FK_87698F2347ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id)');
        $this->addSql('ALTER TABLE AvMission ADD CONSTRAINT FK_87698F23BE6CAE90 FOREIGN KEY (mission_id) REFERENCES Mission (id)');
        $this->addSql('ALTER TABLE AvMission ADD CONSTRAINT FK_87698F2385631A3A FOREIGN KEY (avenant_id) REFERENCES Av (id)');
        $this->addSql('ALTER TABLE RepartitionJEH ADD CONSTRAINT FK_5E061BA8BE6CAE90 FOREIGN KEY (mission_id) REFERENCES Mission (id)');
        $this->addSql('ALTER TABLE RepartitionJEH ADD CONSTRAINT FK_5E061BA8CC0EBB8E FOREIGN KEY (avMission_id) REFERENCES AvMission (id)');
        $this->addSql('ALTER TABLE competence_membre ADD CONSTRAINT FK_BF579E3515761DAB FOREIGN KEY (competence_id) REFERENCES Competence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE competence_membre ADD CONSTRAINT FK_BF579E356A99F74A FOREIGN KEY (membre_id) REFERENCES Membre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE competence_etude ADD CONSTRAINT FK_97FCF8C215761DAB FOREIGN KEY (competence_id) REFERENCES Competence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE competence_etude ADD CONSTRAINT FK_97FCF8C247ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Appel ADD CONSTRAINT FK_C0F1FCB935E10B95 FOREIGN KEY (suiveur_id) REFERENCES Membre (id)');
        $this->addSql('ALTER TABLE Appel ADD CONSTRAINT FK_C0F1FCB91B65292 FOREIGN KEY (employe_id) REFERENCES Employe (id)');
        $this->addSql('ALTER TABLE Appel ADD CONSTRAINT FK_C0F1FCB9D182060A FOREIGN KEY (prospect_id) REFERENCES Prospect (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Personne DROP FOREIGN KEY FK_F6B8ABB9A76ED395');
        $this->addSql('ALTER TABLE Comment DROP FOREIGN KEY FK_5BC96BF0F675F31B');
        $this->addSql('ALTER TABLE RelatedDocument DROP FOREIGN KEY FK_E28BFD66C33F7837');
        $this->addSql('ALTER TABLE Document DROP FOREIGN KEY FK_211FE8203256915B');
        $this->addSql('ALTER TABLE FactureDetail DROP FOREIGN KEY FK_82D8557B7F2DEE08');
        $this->addSql('ALTER TABLE Facture DROP FOREIGN KEY FK_313B5D8CD4F76809');
        $this->addSql('ALTER TABLE bv_cotisationurssaf DROP FOREIGN KEY FK_C27204B312CF54B5');
        $this->addSql('ALTER TABLE bv_cotisationurssaf DROP FOREIGN KEY FK_C27204B3F2843052');
        $this->addSql('ALTER TABLE NoteDeFraisDetail DROP FOREIGN KEY FK_26A881021B119F3E');
        $this->addSql('ALTER TABLE FactureDetail DROP FOREIGN KEY FK_82D8557BF2C56620');
        $this->addSql('ALTER TABLE NoteDeFraisDetail DROP FOREIGN KEY FK_26A88102F2C56620');
        $this->addSql('ALTER TABLE BV DROP FOREIGN KEY FK_19ECBB9772FE1FD');
        $this->addSql('ALTER TABLE RelatedDocument DROP FOREIGN KEY FK_E28BFD665200282E');
        $this->addSql('ALTER TABLE formation_formateurs DROP FOREIGN KEY FK_528364D5200282E');
        $this->addSql('ALTER TABLE formation_membresPresents DROP FOREIGN KEY FK_232FA65D5200282E');
        $this->addSql('ALTER TABLE Personne DROP FOREIGN KEY FK_F6B8ABB91B65292');
        $this->addSql('ALTER TABLE Appel DROP FOREIGN KEY FK_C0F1FCB91B65292');
        $this->addSql('ALTER TABLE RelatedDocument DROP FOREIGN KEY FK_E28BFD666A99F74A');
        $this->addSql('ALTER TABLE Mandat DROP FOREIGN KEY FK_19FFEAE36A99F74A');
        $this->addSql('ALTER TABLE Personne DROP FOREIGN KEY FK_F6B8ABB96A99F74A');
        $this->addSql('ALTER TABLE Mission DROP FOREIGN KEY FK_5FDACBA0593919F0');
        $this->addSql('ALTER TABLE Mission DROP FOREIGN KEY FK_5FDACBA0AB9A1716');
        $this->addSql('ALTER TABLE competence_membre DROP FOREIGN KEY FK_BF579E356A99F74A');
        $this->addSql('ALTER TABLE Appel DROP FOREIGN KEY FK_C0F1FCB935E10B95');
        $this->addSql('ALTER TABLE Mandat DROP FOREIGN KEY FK_19FFEAE3A0905086');
        $this->addSql('ALTER TABLE Membre DROP FOREIGN KEY FK_F118FE1F180AA129');
        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A6479A21BD112');
        $this->addSql('ALTER TABLE Document DROP FOREIGN KEY FK_211FE820FEEAB26A');
        $this->addSql('ALTER TABLE NoteDeFrais DROP FOREIGN KEY FK_30CFBBFC95A6EE59');
        $this->addSql('ALTER TABLE formation_formateurs DROP FOREIGN KEY FK_528364DA21BD112');
        $this->addSql('ALTER TABLE formation_membresPresents DROP FOREIGN KEY FK_232FA65DA21BD112');
        $this->addSql('ALTER TABLE Employe DROP FOREIGN KEY FK_37B9EA25A21BD112');
        $this->addSql('ALTER TABLE Membre DROP FOREIGN KEY FK_F118FE1FA21BD112');
        $this->addSql('ALTER TABLE Av DROP FOREIGN KEY FK_11DDB8B2C71184C3');
        $this->addSql('ALTER TABLE Av DROP FOREIGN KEY FK_11DDB8B2D5A42B2D');
        $this->addSql('ALTER TABLE Ap DROP FOREIGN KEY FK_F8BE1D87C71184C3');
        $this->addSql('ALTER TABLE Ap DROP FOREIGN KEY FK_F8BE1D87D5A42B2D');
        $this->addSql('ALTER TABLE Ap DROP FOREIGN KEY FK_F8BE1D872DCE3B21');
        $this->addSql('ALTER TABLE Mission DROP FOREIGN KEY FK_5FDACBA0C71184C3');
        $this->addSql('ALTER TABLE Mission DROP FOREIGN KEY FK_5FDACBA0D5A42B2D');
        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F862035E10B95');
        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F86207E803A77');
        $this->addSql('ALTER TABLE Cc DROP FOREIGN KEY FK_4E363EDBC71184C3');
        $this->addSql('ALTER TABLE Cc DROP FOREIGN KEY FK_4E363EDBD5A42B2D');
        $this->addSql('ALTER TABLE ClientContact DROP FOREIGN KEY FK_9C3438615302E431');
        $this->addSql('ALTER TABLE ProcesVerbal DROP FOREIGN KEY FK_D8EBE2BFC71184C3');
        $this->addSql('ALTER TABLE ProcesVerbal DROP FOREIGN KEY FK_D8EBE2BFD5A42B2D');
        $this->addSql('ALTER TABLE AvMission DROP FOREIGN KEY FK_87698F23C71184C3');
        $this->addSql('ALTER TABLE AvMission DROP FOREIGN KEY FK_87698F23D5A42B2D');
        $this->addSql('ALTER TABLE RelatedDocument DROP FOREIGN KEY FK_E28BFD66D182060A');
        $this->addSql('ALTER TABLE Facture DROP FOREIGN KEY FK_313B5D8C5AF81F68');
        $this->addSql('ALTER TABLE Employe DROP FOREIGN KEY FK_37B9EA25D182060A');
        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620D182060A');
        $this->addSql('ALTER TABLE Appel DROP FOREIGN KEY FK_C0F1FCB9D182060A');
        $this->addSql('ALTER TABLE Prospect DROP FOREIGN KEY FK_30B8EE2BE2904019');
        $this->addSql('ALTER TABLE Comment DROP FOREIGN KEY FK_5BC96BF0E2904019');
        $this->addSql('ALTER TABLE Av DROP FOREIGN KEY FK_11DDB8B2E2904019');
        $this->addSql('ALTER TABLE Ap DROP FOREIGN KEY FK_F8BE1D87E2904019');
        $this->addSql('ALTER TABLE Mission DROP FOREIGN KEY FK_5FDACBA0E2904019');
        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620E2904019');
        $this->addSql('ALTER TABLE Cc DROP FOREIGN KEY FK_4E363EDBE2904019');
        $this->addSql('ALTER TABLE ClientContact DROP FOREIGN KEY FK_9C343861E2904019');
        $this->addSql('ALTER TABLE ProcesVerbal DROP FOREIGN KEY FK_D8EBE2BFE2904019');
        $this->addSql('ALTER TABLE AvMission DROP FOREIGN KEY FK_87698F23E2904019');
        $this->addSql('ALTER TABLE Phase DROP FOREIGN KEY FK_707CF9CF85631A3A');
        $this->addSql('ALTER TABLE AvMission DROP FOREIGN KEY FK_87698F2385631A3A');
        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620904F155E');
        $this->addSql('ALTER TABLE Phase DROP FOREIGN KEY FK_707CF9CF7A45358C');
        $this->addSql('ALTER TABLE BV DROP FOREIGN KEY FK_19ECBB9BE6CAE90');
        $this->addSql('ALTER TABLE Phase DROP FOREIGN KEY FK_707CF9CFBE6CAE90');
        $this->addSql('ALTER TABLE AvMission DROP FOREIGN KEY FK_87698F23BE6CAE90');
        $this->addSql('ALTER TABLE RepartitionJEH DROP FOREIGN KEY FK_5E061BA8BE6CAE90');
        $this->addSql('ALTER TABLE RelatedDocument DROP FOREIGN KEY FK_E28BFD6647ABD362');
        $this->addSql('ALTER TABLE Facture DROP FOREIGN KEY FK_313B5D8C47ABD362');
        $this->addSql('ALTER TABLE Suivi DROP FOREIGN KEY FK_EF7DE58B47ABD362');
        $this->addSql('ALTER TABLE Av DROP FOREIGN KEY FK_11DDB8B247ABD362');
        $this->addSql('ALTER TABLE Ap DROP FOREIGN KEY FK_F8BE1D8747ABD362');
        $this->addSql('ALTER TABLE GroupePhases DROP FOREIGN KEY FK_D70195E747ABD362');
        $this->addSql('ALTER TABLE Mission DROP FOREIGN KEY FK_5FDACBA047ABD362');
        $this->addSql('ALTER TABLE Cc DROP FOREIGN KEY FK_4E363EDB47ABD362');
        $this->addSql('ALTER TABLE ClientContact DROP FOREIGN KEY FK_9C34386147ABD362');
        $this->addSql('ALTER TABLE ProcesVerbal DROP FOREIGN KEY FK_D8EBE2BF47ABD362');
        $this->addSql('ALTER TABLE Phase DROP FOREIGN KEY FK_707CF9CF47ABD362');
        $this->addSql('ALTER TABLE AvMission DROP FOREIGN KEY FK_87698F2347ABD362');
        $this->addSql('ALTER TABLE competence_etude DROP FOREIGN KEY FK_97FCF8C247ABD362');
        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620A823BE4F');
        $this->addSql('ALTER TABLE RepartitionJEH DROP FOREIGN KEY FK_5E061BA8CC0EBB8E');
        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F86204C79389A');
        $this->addSql('ALTER TABLE competence_membre DROP FOREIGN KEY FK_BF579E3515761DAB');
        $this->addSql('ALTER TABLE competence_etude DROP FOREIGN KEY FK_97FCF8C215761DAB');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE Document');
        $this->addSql('DROP TABLE RelatedDocument');
        $this->addSql('DROP TABLE AdminParam');
        $this->addSql('DROP TABLE Indicateur');
        $this->addSql('DROP TABLE Facture');
        $this->addSql('DROP TABLE FactureDetail');
        $this->addSql('DROP TABLE CotisationURSSAF');
        $this->addSql('DROP TABLE BV');
        $this->addSql('DROP TABLE bv_cotisationurssaf');
        $this->addSql('DROP TABLE NoteDeFrais');
        $this->addSql('DROP TABLE Compte');
        $this->addSql('DROP TABLE BaseURSSAF');
        $this->addSql('DROP TABLE NoteDeFraisDetail');
        $this->addSql('DROP TABLE Formation');
        $this->addSql('DROP TABLE formation_formateurs');
        $this->addSql('DROP TABLE formation_membresPresents');
        $this->addSql('DROP TABLE Employe');
        $this->addSql('DROP TABLE Membre');
        $this->addSql('DROP TABLE Mandat');
        $this->addSql('DROP TABLE Poste');
        $this->addSql('DROP TABLE Filiere');
        $this->addSql('DROP TABLE Personne');
        $this->addSql('DROP TABLE Prospect');
        $this->addSql('DROP TABLE Thread');
        $this->addSql('DROP TABLE Comment');
        $this->addSql('DROP TABLE Suivi');
        $this->addSql('DROP TABLE Av');
        $this->addSql('DROP TABLE Ap');
        $this->addSql('DROP TABLE GroupePhases');
        $this->addSql('DROP TABLE Mission');
        $this->addSql('DROP TABLE Etude');
        $this->addSql('DROP TABLE Cc');
        $this->addSql('DROP TABLE ClientContact');
        $this->addSql('DROP TABLE ProcesVerbal');
        $this->addSql('DROP TABLE Phase');
        $this->addSql('DROP TABLE AvMission');
        $this->addSql('DROP TABLE RepartitionJEH');
        $this->addSql('DROP TABLE DomaineCompetence');
        $this->addSql('DROP TABLE Competence');
        $this->addSql('DROP TABLE competence_membre');
        $this->addSql('DROP TABLE competence_etude');
        $this->addSql('DROP TABLE Appel');
    }
}
