<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210131195307 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX idx_name ON permission_group (company_id, name)');
        $this->addSql('CREATE UNIQUE INDEX idx_name ON supplier (company_id, name)');
        $this->addSql('ALTER TABLE user ADD mobile_push_id VARCHAR(255) DEFAULT NULL, ADD web_push_id VARCHAR(255) NOT NULL, CHANGE double_opt_in double_opt_in TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX idx_name ON permission_group');
        $this->addSql('DROP INDEX idx_name ON supplier');
        $this->addSql('ALTER TABLE user DROP mobile_push_id, DROP web_push_id, CHANGE double_opt_in double_opt_in TINYINT(1) NOT NULL');
    }
}
