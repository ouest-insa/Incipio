<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Reverse owning side between Etude and (AP/CC)
 * Populate the newly created fields from the already existent data.
 */
class Version20170320153305 extends AbstractMigration
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
                    WHERE TABLE_SCHEMA = "jeyser" AND TABLE_NAME = "Etude" AND COLUMN_NAME = "ap_id"')->fetch(),
            'Etude.ap_id column already available');

        $this->addSql('ALTER TABLE Etude ADD ap_id INT DEFAULT NULL, ADD cc_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F8620904F155E FOREIGN KEY (ap_id) REFERENCES Ap (id)');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F8620A823BE4F FOREIGN KEY (cc_id) REFERENCES Cc (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC1F8620904F155E ON Etude (ap_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DC1F8620A823BE4F ON Etude (cc_id)');

        $this->addSql('UPDATE Etude SET ap_id = (SELECT id  FROM Ap WHERE etude_id = Etude.id)');
        $this->addSql('UPDATE Etude SET cc_id = (SELECT id  FROM Cc WHERE etude_id = Etude.id) ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620904F155E');
        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620A823BE4F');
        $this->addSql('DROP INDEX UNIQ_DC1F8620904F155E ON Etude');
        $this->addSql('DROP INDEX UNIQ_DC1F8620A823BE4F ON Etude');
        $this->addSql('ALTER TABLE Etude DROP ap_id, DROP cc_id');
    }
}
