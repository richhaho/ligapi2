<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210308094530 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material_for_web ADD is_archived TINYINT(1) NOT NULL, CHANGE permanent_inventory permanent_inventory TINYINT(1) NOT NULL');
        $this->addSql('DROP INDEX IDX_B446A4E8FEC530A91276924F ON search_index');
        $this->addSql('CREATE UNIQUE INDEX idx_itemId ON search_index (company_id, entity_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material_for_web DROP is_archived, CHANGE permanent_inventory permanent_inventory VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX idx_itemId ON search_index');
        $this->addSql('CREATE FULLTEXT INDEX IDX_B446A4E8FEC530A91276924F ON search_index (content, entity_shortname)');
    }
}
