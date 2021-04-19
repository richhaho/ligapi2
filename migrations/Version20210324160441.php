<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210324160441 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE grid_state ADD user_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE grid_state ADD CONSTRAINT FK_26D0D08EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_26D0D08EA76ED395 ON grid_state (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE grid_state DROP FOREIGN KEY FK_26D0D08EA76ED395');
        $this->addSql('DROP INDEX IDX_26D0D08EA76ED395 ON grid_state');
        $this->addSql('ALTER TABLE grid_state DROP user_id');
    }
}
