<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210307091620 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material_for_web CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE item_group item_group VARCHAR(255) DEFAULT NULL, CHANGE main_location main_location VARCHAR(255) DEFAULT NULL, CHANGE main_location_stock main_location_stock DOUBLE PRECISION DEFAULT NULL, CHANGE min_stock min_stock DOUBLE PRECISION DEFAULT NULL, CHANGE max_stock max_stock DOUBLE PRECISION DEFAULT NULL, CHANGE main_location_last_change main_location_last_change VARCHAR(255) DEFAULT NULL, CHANGE order_status order_status VARCHAR(255) DEFAULT NULL, CHANGE thumb thumb VARCHAR(255) DEFAULT NULL, CHANGE main_supplier main_supplier VARCHAR(255) DEFAULT NULL, CHANGE main_supplier_order_number main_supplier_order_number VARCHAR(255) DEFAULT NULL, CHANGE main_supplier_purchasing_price main_supplier_purchasing_price VARCHAR(255) DEFAULT NULL, CHANGE selling_price selling_price VARCHAR(255) DEFAULT NULL, CHANGE order_amount order_amount DOUBLE PRECISION DEFAULT NULL, CHANGE manufacturer_number manufacturer_number VARCHAR(255) DEFAULT NULL, CHANGE manufacturer_name manufacturer_name VARCHAR(255) DEFAULT NULL, CHANGE barcode barcode VARCHAR(255) DEFAULT NULL, CHANGE unit unit VARCHAR(255) DEFAULT NULL, CHANGE permission_group permission_group VARCHAR(255) DEFAULT NULL, CHANGE additional_stock additional_stock DOUBLE PRECISION DEFAULT NULL, CHANGE total_stock total_stock DOUBLE PRECISION DEFAULT NULL, CHANGE note note VARCHAR(255) DEFAULT NULL, CHANGE usable_till usable_till VARCHAR(255) DEFAULT NULL, CHANGE unit_alt unit_alt VARCHAR(255) DEFAULT NULL, CHANGE unit_conversion unit_conversion DOUBLE PRECISION DEFAULT NULL, CHANGE custom_fields custom_fields VARCHAR(255) DEFAULT NULL, CHANGE additional_search_terms additional_search_terms LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material_for_web CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE item_group item_group VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE main_location main_location VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE main_location_stock main_location_stock DOUBLE PRECISION NOT NULL, CHANGE min_stock min_stock DOUBLE PRECISION NOT NULL, CHANGE max_stock max_stock DOUBLE PRECISION NOT NULL, CHANGE main_location_last_change main_location_last_change VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE order_status order_status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE thumb thumb VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE main_supplier main_supplier VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE main_supplier_order_number main_supplier_order_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE main_supplier_purchasing_price main_supplier_purchasing_price VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE selling_price selling_price VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE order_amount order_amount DOUBLE PRECISION NOT NULL, CHANGE manufacturer_number manufacturer_number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE manufacturer_name manufacturer_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE barcode barcode VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE unit unit VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE permission_group permission_group VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE additional_stock additional_stock DOUBLE PRECISION NOT NULL, CHANGE total_stock total_stock DOUBLE PRECISION NOT NULL, CHANGE note note VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE usable_till usable_till VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE unit_alt unit_alt VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE unit_conversion unit_conversion DOUBLE PRECISION NOT NULL, CHANGE custom_fields custom_fields VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE additional_search_terms additional_search_terms LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
