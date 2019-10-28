<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191028191038 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Ce (id INT AUTO_INCREMENT NOT NULL, thread_id VARCHAR(255) DEFAULT NULL, signataire1_id INT DEFAULT NULL, signataire2_id INT DEFAULT NULL, etude_id INT NOT NULL, contact_id INT DEFAULT NULL, version INT DEFAULT NULL, redige TINYINT(1) DEFAULT NULL, relu TINYINT(1) DEFAULT NULL, spt1 TINYINT(1) DEFAULT NULL, spt2 TINYINT(1) DEFAULT NULL, dateSignature DATETIME DEFAULT NULL, envoye TINYINT(1) DEFAULT NULL, receptionne TINYINT(1) DEFAULT NULL, generer INT DEFAULT NULL, nbrDev INT DEFAULT NULL, deonto TINYINT(1) DEFAULT NULL, UNIQUE INDEX UNIQ_A7559BEEE2904019 (thread_id), INDEX IDX_A7559BEEC71184C3 (signataire1_id), INDEX IDX_A7559BEED5A42B2D (signataire2_id), UNIQUE INDEX UNIQ_A7559BEE47ABD362 (etude_id), INDEX IDX_A7559BEEE7A1254A (contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Ce ADD CONSTRAINT FK_A7559BEEE2904019 FOREIGN KEY (thread_id) REFERENCES Thread (id)');
        $this->addSql('ALTER TABLE Ce ADD CONSTRAINT FK_A7559BEEC71184C3 FOREIGN KEY (signataire1_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Ce ADD CONSTRAINT FK_A7559BEED5A42B2D FOREIGN KEY (signataire2_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Ce ADD CONSTRAINT FK_A7559BEE47ABD362 FOREIGN KEY (etude_id) REFERENCES Etude (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Ce ADD CONSTRAINT FK_A7559BEEE7A1254A FOREIGN KEY (contact_id) REFERENCES Personne (id)');
        $this->addSql('ALTER TABLE Etude ADD ce_id INT DEFAULT NULL, ADD ceActive TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F86208D48E193 FOREIGN KEY (ce_id) REFERENCES Ce (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC1F86208D48E193 ON Etude (ce_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F86208D48E193');
        $this->addSql('DROP TABLE Ce');
        $this->addSql('DROP INDEX UNIQ_DC1F86208D48E193 ON Etude');
        $this->addSql('ALTER TABLE Etude DROP ce_id, DROP ceActive');
    }
}
