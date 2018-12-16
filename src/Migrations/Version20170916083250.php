<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Remove devco bundle, thus remove Appel table.
 */
class Version20170916083250 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        /**
         * Jeyser got migrations quite late in the project.
         * This check is to keep thing working smoothly on every install: migration is not performed is its result
         * is already there.
         * fetch() equals to an array if the column exist, and false otherwise.
         */
        $this->skipIf(!is_array($this->connection->executeQuery('SELECT * FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = "jeyser" AND TABLE_NAME = "Appel"')->fetch()), 'Table Appel already dropped');

        $this->addSql('DROP TABLE Appel');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Appel (id INT AUTO_INCREMENT NOT NULL, suiveur_id INT NOT NULL, employe_id INT NOT NULL, prospect_id INT NOT NULL, noteAppel LONGTEXT NOT NULL COLLATE utf8_unicode_ci, dateAppel DATE NOT NULL, aRappeller TINYINT(1) DEFAULT \'1\' NOT NULL, dateRappel DATE DEFAULT NULL, INDEX IDX_C0F1FCB935E10B95 (suiveur_id), INDEX IDX_C0F1FCB91B65292 (employe_id), INDEX IDX_C0F1FCB9D182060A (prospect_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Appel ADD CONSTRAINT FK_C0F1FCB91B65292 FOREIGN KEY (employe_id) REFERENCES Employe (id)');
        $this->addSql('ALTER TABLE Appel ADD CONSTRAINT FK_C0F1FCB935E10B95 FOREIGN KEY (suiveur_id) REFERENCES Membre (id)');
        $this->addSql('ALTER TABLE Appel ADD CONSTRAINT FK_C0F1FCB9D182060A FOREIGN KEY (prospect_id) REFERENCES Prospect (id)');
    }
}
