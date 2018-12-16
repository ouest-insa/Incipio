<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add namingConvention Parameter to available parameters
 */
class Version20170603140214 extends AbstractMigration
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
        $this->skipIf(is_array($this->connection->executeQuery('SELECT * FROM AdminParam where name = "namingConvention"')
            ->fetch()),
            'namingConvention already in AdminParam table');

        $query = 'INSERT INTO `AdminParam` (`id`, `name`, `paramType`, `defaultValue`, `required`, `paramLabel`, 
`paramDescription`, `priority`) VALUES (NULL, \'namingConvention\', \'string\', \'nom\', \'1\',
 \'Convention de nommage des documents\', \'Quel champ d\une étude doit être utilisé dans les références à un document ?
Accepte les valeurs numero ou nom\', \'820\')';
        $this->addSql($query);

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE from AdminParam where name = "namingConvention"');

    }
}
