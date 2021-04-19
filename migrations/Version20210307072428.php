<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210307072428 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE material_for_web (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, material_id VARCHAR(255) NOT NULL, item_number VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, item_group VARCHAR(255) NOT NULL, main_location VARCHAR(255) NOT NULL, main_location_stock DOUBLE PRECISION NOT NULL, min_stock DOUBLE PRECISION NOT NULL, max_stock DOUBLE PRECISION NOT NULL, main_location_last_change VARCHAR(255) NOT NULL, order_status VARCHAR(255) NOT NULL, thumb VARCHAR(255) NOT NULL, main_supplier VARCHAR(255) NOT NULL, main_supplier_order_number VARCHAR(255) NOT NULL, main_supplier_purchasing_price VARCHAR(255) NOT NULL, selling_price VARCHAR(255) NOT NULL, order_amount DOUBLE PRECISION NOT NULL, manufacturer_number VARCHAR(255) NOT NULL, manufacturer_name VARCHAR(255) NOT NULL, barcode VARCHAR(255) NOT NULL, unit VARCHAR(255) NOT NULL, permission_group VARCHAR(255) NOT NULL, additional_stock DOUBLE PRECISION NOT NULL, total_stock DOUBLE PRECISION NOT NULL, note VARCHAR(255) NOT NULL, usable_till VARCHAR(255) NOT NULL, unit_alt VARCHAR(255) NOT NULL, unit_conversion DOUBLE PRECISION NOT NULL, permanent_inventory VARCHAR(255) NOT NULL, custom_fields VARCHAR(255) NOT NULL, INDEX IDX_C7C68D99979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE material_for_web ADD CONSTRAINT FK_C7C68D99979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE material_for_web');
    }
}
