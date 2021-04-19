<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201118132437 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_B446A4E8FEC530A9C412EE02 ON search_index');
        $this->addSql('ALTER TABLE search_index CHANGE entity_type entity_shortname VARCHAR(255) NOT NULL');
        $this->addSql('CREATE FULLTEXT INDEX IDX_B446A4E8FEC530A91276924F ON search_index (content, entity_shortname)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_B446A4E8FEC530A91276924F ON search_index');
        $this->addSql('ALTER TABLE search_index CHANGE entity_shortname entity_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE FULLTEXT INDEX IDX_B446A4E8FEC530A9C412EE02 ON search_index (content, entity_type)');
    }
}
