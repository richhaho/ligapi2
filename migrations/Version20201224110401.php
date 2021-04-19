<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201224110401 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material ADD last_material_order_position_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75955E841800 FOREIGN KEY (last_material_order_position_id) REFERENCES material_order_position (id)');
        $this->addSql('CREATE INDEX IDX_7CBE75955E841800 ON material (last_material_order_position_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE75955E841800');
        $this->addSql('DROP INDEX IDX_7CBE75955E841800 ON material');
        $this->addSql('ALTER TABLE material DROP last_material_order_position_id');
    }
}
