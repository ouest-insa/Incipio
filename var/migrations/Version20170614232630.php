<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix typo in parameter anneeCreation
 */
class Version20170614232630 extends AbstractMigration
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
        $this->skipIf($this->connection->executeQuery('SELECT * FROM AdminParam where name = "anneeCreation"')->fetch(),
            'anneeCreation already in AdminParam table');


        $this->addSql('Update `AdminParam` set name = "anneeCreation" where name = "anneCreation"');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('Update `AdminParam` set name = "anneCreation" where name = "anneeCreation"');

    }
}
