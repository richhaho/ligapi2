<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210120185948 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE owner_change (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, tool_id VARCHAR(255) DEFAULT NULL, keyy_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', new_owner_name VARCHAR(255) NOT NULL, INDEX IDX_E5ADD27E979B1AD6 (company_id), INDEX IDX_E5ADD27E8F7B22CC (tool_id), INDEX IDX_E5ADD27E37112802 (keyy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE owner_change ADD CONSTRAINT FK_E5ADD27E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE owner_change ADD CONSTRAINT FK_E5ADD27E8F7B22CC FOREIGN KEY (tool_id) REFERENCES tool (id)');
        $this->addSql('ALTER TABLE owner_change ADD CONSTRAINT FK_E5ADD27E37112802 FOREIGN KEY (keyy_id) REFERENCES keyy (id)');
        $this->addSql('ALTER TABLE grid_state CHANGE column_state column_state LONGTEXT NOT NULL, CHANGE sort_state sort_state LONGTEXT NOT NULL, CHANGE filter_state filter_state LONGTEXT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE owner_change');
        $this->addSql('ALTER TABLE grid_state CHANGE column_state column_state JSON NOT NULL, CHANGE sort_state sort_state JSON NOT NULL, CHANGE filter_state filter_state JSON NOT NULL');
    }
}
