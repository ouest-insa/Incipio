<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200317144516 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE NoteDeFrais CHANGE mandat mandat INT NOT NULL');
        $this->addSql('ALTER TABLE NoteDeFraisDetail ADD peageHT NUMERIC(6, 2) DEFAULT NULL, ADD tvaPeages NUMERIC(6, 2) DEFAULT \'20\', CHANGE tauxTVA tauxTVA NUMERIC(6, 2) DEFAULT \'20\', CHANGE tauxKm tauxKm INT DEFAULT 14');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE NoteDeFrais CHANGE mandat mandat INT DEFAULT 2020 NOT NULL');
        $this->addSql('ALTER TABLE NoteDeFraisDetail DROP peageHT, DROP tvaPeages, CHANGE tauxTVA tauxTVA NUMERIC(6, 2) DEFAULT NULL, CHANGE tauxKm tauxKm INT DEFAULT NULL');
    }
}
