<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180704185452 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Document DROP FOREIGN KEY FK_211FE8203256915B');
        $this->addSql('ALTER TABLE Document ADD CONSTRAINT FK_211FE8203256915B FOREIGN KEY (relation_id) REFERENCES RelatedDocument (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Document DROP FOREIGN KEY FK_211FE8203256915B');
        $this->addSql('ALTER TABLE Document ADD CONSTRAINT FK_211FE8203256915B FOREIGN KEY (relation_id) REFERENCES RelatedDocument (id)');
    }
}
