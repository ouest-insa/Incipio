<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Cascade delete between etude and CC.
 */
class Version20180110191604 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.');

        // no easy backward check to perform, besides, can be performed multiple times

        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620A823BE4F');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F8620A823BE4F FOREIGN KEY (cc_id) REFERENCES Cc (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620A823BE4F');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F8620A823BE4F FOREIGN KEY (cc_id) REFERENCES Cc (id)');
    }
}
