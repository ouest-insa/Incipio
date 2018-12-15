<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170421211323 extends AbstractMigration
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
                    WHERE TABLE_SCHEMA = "jeyser" AND TABLE_NAME = "Membre" AND COLUMN_NAME = "commentaire"')->fetch(),
            'Membre.commentaire column already available.');

        $this->addSql('ALTER TABLE Membre ADD commentaire VARCHAR(500) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Membre DROP commentaire');
    }
}
