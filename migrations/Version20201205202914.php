<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201205202914 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material ADD order_status_change_user_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595E22AB20A FOREIGN KEY (order_status_change_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7CBE7595E22AB20A ON material (order_status_change_user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595E22AB20A');
        $this->addSql('DROP INDEX IDX_7CBE7595E22AB20A ON material');
        $this->addSql('ALTER TABLE material DROP order_status_change_user_id');
    }
}
