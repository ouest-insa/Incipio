<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170327090019 extends AbstractMigration
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
        $this->skipIf($this->connection->executeQuery('SELECT * FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = "jeyser" AND TABLE_NAME = "Etude" AND COLUMN_NAME = "suiveurQualite_id"')
            ->fetch(),
            'Etude.suiveurQualite_id column already available');

        $this->addSql('ALTER TABLE Etude ADD suiveurQualite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F86207E803A77 FOREIGN KEY (suiveurQualite_id) REFERENCES Personne (id)');
        $this->addSql('CREATE INDEX IDX_DC1F86207E803A77 ON Etude (suiveurQualite_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F86207E803A77');
        $this->addSql('DROP INDEX IDX_DC1F86207E803A77 ON Etude');
        $this->addSql('ALTER TABLE Etude DROP suiveurQualite_id');
    }
}
