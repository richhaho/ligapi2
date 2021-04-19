<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210224114313 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX idx_consignmentNumber ON consignment (company_id, consignment_number)');
        $this->addSql('CREATE UNIQUE INDEX idx_directOrderNumber ON direct_order (company_id, direct_order_number)');
        $this->addSql('CREATE UNIQUE INDEX idx_itemNumber ON keyy (company_id, item_number)');
        $this->addSql('CREATE UNIQUE INDEX idx_materialOrderNumber ON material_order (company_id, material_order_number)');
        $this->addSql('CREATE UNIQUE INDEX idx_itemNumber ON tool (company_id, item_number)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX idx_consignmentNumber ON consignment');
        $this->addSql('DROP INDEX idx_directOrderNumber ON direct_order');
        $this->addSql('DROP INDEX idx_itemNumber ON keyy');
        $this->addSql('DROP INDEX idx_materialOrderNumber ON material_order');
        $this->addSql('DROP INDEX idx_itemNumber ON tool');
    }
}
