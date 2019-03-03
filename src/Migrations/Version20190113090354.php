<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix etude removal
 */
final class Version20190113090354 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620904F155E');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F8620904F155E FOREIGN KEY (ap_id) REFERENCES Ap (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Etude DROP FOREIGN KEY FK_DC1F8620904F155E');
        $this->addSql('ALTER TABLE Etude ADD CONSTRAINT FK_DC1F8620904F155E FOREIGN KEY (ap_id) REFERENCES Ap (id)');
    }
}
