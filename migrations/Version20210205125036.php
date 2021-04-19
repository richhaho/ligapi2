<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210205125036 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE location DROP INDEX IDX_5E9E89CBA76ED395, ADD UNIQUE INDEX UNIQ_5E9E89CBA76ED395 (user_id)');
        $this->addSql('ALTER TABLE material_location ADD project_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE material_location ADD CONSTRAINT FK_1846AD69166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_1846AD69166D1F9C ON material_location (project_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE location DROP INDEX UNIQ_5E9E89CBA76ED395, ADD INDEX IDX_5E9E89CBA76ED395 (user_id)');
        $this->addSql('ALTER TABLE material_location DROP FOREIGN KEY FK_1846AD69166D1F9C');
        $this->addSql('DROP INDEX IDX_1846AD69166D1F9C ON material_location');
        $this->addSql('ALTER TABLE material_location DROP project_id');
    }
}
