<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201107160433 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE supplier (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, connected_supplier_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', origin VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, customer_number VARCHAR(255) DEFAULT NULL, web_shop_login VARCHAR(255) DEFAULT NULL, web_shop_password VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, fax VARCHAR(255) DEFAULT NULL, responsible_person VARCHAR(255) DEFAULT NULL, email_salutation VARCHAR(255) DEFAULT NULL, INDEX IDX_9B2A6C7E979B1AD6 (company_id), INDEX IDX_9B2A6C7ED9DC8CA7 (connected_supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_source (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, material_id VARCHAR(255) DEFAULT NULL, supplier_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', origin VARCHAR(255) NOT NULL, order_number VARCHAR(255) NOT NULL, note VARCHAR(255) DEFAULT NULL, priority INT NOT NULL, amount_per_purchase_unit VARCHAR(255) DEFAULT NULL, price VARCHAR(255) COMMENT \'(DC2Type:money)\' DEFAULT NULL, last_price_update DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_crawler_set DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', crawlerStatus_value VARCHAR(10) DEFAULT NULL, INDEX IDX_9C056FA6979B1AD6 (company_id), INDEX IDX_9C056FA6E308AC6F (material_id), INDEX IDX_9C056FA62ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, item_group_id VARCHAR(255) DEFAULT NULL, permission_group_id VARCHAR(255) DEFAULT NULL, crawler_supplier_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_archived TINYINT(1) NOT NULL, permanent_inventory TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, note LONGTEXT DEFAULT NULL, item_number VARCHAR(50) NOT NULL, manufacturer_number VARCHAR(255) DEFAULT NULL, manufacturer_name VARCHAR(255) DEFAULT NULL, barcode VARCHAR(255) DEFAULT NULL, unit VARCHAR(255) DEFAULT NULL, unit_alt VARCHAR(255) DEFAULT NULL, order_amount DOUBLE PRECISION DEFAULT NULL, usable_till DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', unit_conversion DOUBLE PRECISION DEFAULT NULL, order_status_note VARCHAR(255) DEFAULT NULL, order_status_change_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', origin VARCHAR(255) NOT NULL, selling_price VARCHAR(255) COMMENT \'(DC2Type:money)\' DEFAULT NULL, files JSON NOT NULL, custom_fields JSON DEFAULT NULL, crawler_search_term VARCHAR(255) DEFAULT NULL, orderStatus_value VARCHAR(10) DEFAULT NULL, crawlerStatus_value VARCHAR(10) DEFAULT NULL, INDEX IDX_7CBE7595979B1AD6 (company_id), INDEX IDX_7CBE75959259118C (item_group_id), INDEX IDX_7CBE7595B6C0CF1 (permission_group_id), INDEX IDX_7CBE7595D21A2081 (crawler_supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material_order (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, supplier_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivery_note VARCHAR(255) DEFAULT NULL, file_link VARCHAR(255) DEFAULT NULL, materialOrderStatus_value VARCHAR(10) DEFAULT NULL, materialOrderType_value VARCHAR(10) DEFAULT NULL, INDEX IDX_A3CE3FD6979B1AD6 (company_id), INDEX IDX_A3CE3FD62ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tool (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, home_id VARCHAR(255) DEFAULT NULL, owner_id VARCHAR(255) DEFAULT NULL, item_group_id VARCHAR(255) DEFAULT NULL, permission_group_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_archived TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, purchasing_price DOUBLE PRECISION DEFAULT NULL, purchasing_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', note LONGTEXT DEFAULT NULL, is_broken TINYINT(1) NOT NULL, item_number VARCHAR(50) NOT NULL, manufacturer_number VARCHAR(255) DEFAULT NULL, manufacturer_name VARCHAR(255) DEFAULT NULL, barcode VARCHAR(255) DEFAULT NULL, blecode VARCHAR(255) DEFAULT NULL, origin VARCHAR(50) NOT NULL, files JSON NOT NULL, custom_fields JSON DEFAULT NULL, INDEX IDX_20F33ED1979B1AD6 (company_id), INDEX IDX_20F33ED128CDC89C (home_id), INDEX IDX_20F33ED17E3C61F9 (owner_id), INDEX IDX_20F33ED19259118C (item_group_id), INDEX IDX_20F33ED1B6C0CF1 (permission_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission_group (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, INDEX IDX_BB4729B6979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consignment (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, project_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, location_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) DEFAULT NULL, note VARCHAR(255) DEFAULT NULL, INDEX IDX_32B8F88C979B1AD6 (company_id), INDEX IDX_32B8F88C166D1F9C (project_id), INDEX IDX_32B8F88CA76ED395 (user_id), INDEX IDX_32B8F88C64D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material_order_position (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, order_source_id VARCHAR(255) DEFAULT NULL, material_order_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount DOUBLE PRECISION NOT NULL, status_message VARCHAR(255) DEFAULT NULL, INDEX IDX_50B65F61979B1AD6 (company_id), INDEX IDX_50B65F6129BB6799 (order_source_id), INDEX IDX_50B65F61CB47499A (material_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consignment_item (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, consignment_id VARCHAR(255) DEFAULT NULL, material_id VARCHAR(255) DEFAULT NULL, tool_id VARCHAR(255) DEFAULT NULL, keyy_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount DOUBLE PRECISION DEFAULT NULL, consigned_amount DOUBLE PRECISION DEFAULT NULL, consignmentItemStatus_value VARCHAR(10) DEFAULT NULL, INDEX IDX_53C7CD07979B1AD6 (company_id), INDEX IDX_53C7CD0797B177ED (consignment_id), INDEX IDX_53C7CD07E308AC6F (material_id), INDEX IDX_53C7CD078F7B22CC (tool_id), INDEX IDX_53C7CD0737112802 (keyy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, permissions JSON NOT NULL, is_admin TINYINT(1) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, password_reset_token VARCHAR(255) DEFAULT NULL, password_reset_expires INT DEFAULT NULL, refresh_token VARCHAR(255) DEFAULT NULL, device_uuid VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE connected_supplier (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, responsible_id VARCHAR(255) DEFAULT NULL, tool_id VARCHAR(255) DEFAULT NULL, material_id VARCHAR(255) DEFAULT NULL, keyy_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', topic VARCHAR(255) NOT NULL, details VARCHAR(255) DEFAULT NULL, start_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', due_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', repeat_after_days INT DEFAULT NULL, priority INT DEFAULT NULL, files JSON NOT NULL, taskStatus_value VARCHAR(10) NOT NULL, INDEX IDX_527EDB25979B1AD6 (company_id), INDEX IDX_527EDB25602AD315 (responsible_id), INDEX IDX_527EDB258F7B22CC (tool_id), INDEX IDX_527EDB25E308AC6F (material_id), INDEX IDX_527EDB2537112802 (keyy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, street VARCHAR(255) DEFAULT NULL, zip VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, shipping_street VARCHAR(255) DEFAULT NULL, shipping_zip VARCHAR(255) DEFAULT NULL, shipping_city VARCHAR(255) DEFAULT NULL, shipping_country VARCHAR(255) DEFAULT NULL, INDEX IDX_81398E09979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, customer_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, INDEX IDX_2FB3D0EE979B1AD6 (company_id), INDEX IDX_2FB3D0EE9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, street VARCHAR(255) DEFAULT NULL, zip VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, fax VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, collection_locations LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', current_material_label INT NOT NULL, custom_material_name VARCHAR(255) DEFAULT NULL, custom_tool_name VARCHAR(255) DEFAULT NULL, custom_keyy_name VARCHAR(255) DEFAULT NULL, app_settings JSON DEFAULT NULL, UNIQUE INDEX UNIQ_4FBF094F5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock_change (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, material_location_id VARCHAR(255) DEFAULT NULL, project_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', note VARCHAR(255) DEFAULT NULL, amount VARCHAR(255) DEFAULT NULL, amount_alt VARCHAR(255) DEFAULT NULL, new_current_stock VARCHAR(255) DEFAULT NULL, new_current_stock_alt VARCHAR(255) DEFAULT NULL, document JSON DEFAULT NULL, INDEX IDX_8A712CB6979B1AD6 (company_id), INDEX IDX_8A712CB6A76ED395 (user_id), INDEX IDX_8A712CB6A5511386 (material_location_id), INDEX IDX_8A712CB6166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) DEFAULT NULL, INDEX IDX_5E9E89CB979B1AD6 (company_id), INDEX IDX_5E9E89CBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material_location (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, location_id VARCHAR(255) DEFAULT NULL, material_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', origin VARCHAR(255) NOT NULL, min_stock DOUBLE PRECISION DEFAULT NULL, current_stock DOUBLE PRECISION NOT NULL, current_stock_alt DOUBLE PRECISION NOT NULL, locationCategory_value VARCHAR(10) NOT NULL, INDEX IDX_1846AD69979B1AD6 (company_id), INDEX IDX_1846AD6964D218E (location_id), INDEX IDX_1846AD69E308AC6F (material_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_group (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, itemGroupType_value VARCHAR(10) DEFAULT NULL, INDEX IDX_47675F15979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_field (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) NOT NULL, options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', customFieldType_value VARCHAR(10) NOT NULL, INDEX IDX_98F8BD31979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE change_log (id VARCHAR(255) NOT NULL, company_id VARCHAR(50) NOT NULL, user_id VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', object_class VARCHAR(255) NOT NULL, object_id VARCHAR(40) NOT NULL, property VARCHAR(255) DEFAULT NULL, new_value LONGTEXT DEFAULT NULL, changeAction_value VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keyy (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, home_id VARCHAR(255) DEFAULT NULL, owner_id VARCHAR(255) DEFAULT NULL, permission_group_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount INT DEFAULT NULL, is_archived TINYINT(1) NOT NULL, origin VARCHAR(50) NOT NULL, item_number VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, note LONGTEXT DEFAULT NULL, files JSON NOT NULL, custom_fields JSON DEFAULT NULL, INDEX IDX_545BADF2979B1AD6 (company_id), INDEX IDX_545BADF228CDC89C (home_id), INDEX IDX_545BADF27E3C61F9 (owner_id), INDEX IDX_545BADF2B6C0CF1 (permission_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supplier ADD CONSTRAINT FK_9B2A6C7E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE supplier ADD CONSTRAINT FK_9B2A6C7ED9DC8CA7 FOREIGN KEY (connected_supplier_id) REFERENCES connected_supplier (id)');
        $this->addSql('ALTER TABLE order_source ADD CONSTRAINT FK_9C056FA6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE order_source ADD CONSTRAINT FK_9C056FA6E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE order_source ADD CONSTRAINT FK_9C056FA62ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75959259118C FOREIGN KEY (item_group_id) REFERENCES item_group (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595B6C0CF1 FOREIGN KEY (permission_group_id) REFERENCES permission_group (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595D21A2081 FOREIGN KEY (crawler_supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE material_order ADD CONSTRAINT FK_A3CE3FD6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE material_order ADD CONSTRAINT FK_A3CE3FD62ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED128CDC89C FOREIGN KEY (home_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED17E3C61F9 FOREIGN KEY (owner_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED19259118C FOREIGN KEY (item_group_id) REFERENCES item_group (id)');
        $this->addSql('ALTER TABLE tool ADD CONSTRAINT FK_20F33ED1B6C0CF1 FOREIGN KEY (permission_group_id) REFERENCES permission_group (id)');
        $this->addSql('ALTER TABLE permission_group ADD CONSTRAINT FK_BB4729B6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE consignment ADD CONSTRAINT FK_32B8F88C979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE consignment ADD CONSTRAINT FK_32B8F88C166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE consignment ADD CONSTRAINT FK_32B8F88CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE consignment ADD CONSTRAINT FK_32B8F88C64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE material_order_position ADD CONSTRAINT FK_50B65F61979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE material_order_position ADD CONSTRAINT FK_50B65F6129BB6799 FOREIGN KEY (order_source_id) REFERENCES order_source (id)');
        $this->addSql('ALTER TABLE material_order_position ADD CONSTRAINT FK_50B65F61CB47499A FOREIGN KEY (material_order_id) REFERENCES material_order (id)');
        $this->addSql('ALTER TABLE consignment_item ADD CONSTRAINT FK_53C7CD07979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE consignment_item ADD CONSTRAINT FK_53C7CD0797B177ED FOREIGN KEY (consignment_id) REFERENCES consignment (id)');
        $this->addSql('ALTER TABLE consignment_item ADD CONSTRAINT FK_53C7CD07E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE consignment_item ADD CONSTRAINT FK_53C7CD078F7B22CC FOREIGN KEY (tool_id) REFERENCES tool (id)');
        $this->addSql('ALTER TABLE consignment_item ADD CONSTRAINT FK_53C7CD0737112802 FOREIGN KEY (keyy_id) REFERENCES keyy (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25602AD315 FOREIGN KEY (responsible_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB258F7B22CC FOREIGN KEY (tool_id) REFERENCES tool (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2537112802 FOREIGN KEY (keyy_id) REFERENCES keyy (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE stock_change ADD CONSTRAINT FK_8A712CB6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE stock_change ADD CONSTRAINT FK_8A712CB6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE stock_change ADD CONSTRAINT FK_8A712CB6A5511386 FOREIGN KEY (material_location_id) REFERENCES material_location (id)');
        $this->addSql('ALTER TABLE stock_change ADD CONSTRAINT FK_8A712CB6166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE material_location ADD CONSTRAINT FK_1846AD69979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE material_location ADD CONSTRAINT FK_1846AD6964D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE material_location ADD CONSTRAINT FK_1846AD69E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE item_group ADD CONSTRAINT FK_47675F15979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE custom_field ADD CONSTRAINT FK_98F8BD31979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE keyy ADD CONSTRAINT FK_545BADF2979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE keyy ADD CONSTRAINT FK_545BADF228CDC89C FOREIGN KEY (home_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE keyy ADD CONSTRAINT FK_545BADF27E3C61F9 FOREIGN KEY (owner_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE keyy ADD CONSTRAINT FK_545BADF2B6C0CF1 FOREIGN KEY (permission_group_id) REFERENCES permission_group (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_source DROP FOREIGN KEY FK_9C056FA62ADD6D8C');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595D21A2081');
        $this->addSql('ALTER TABLE material_order DROP FOREIGN KEY FK_A3CE3FD62ADD6D8C');
        $this->addSql('ALTER TABLE material_order_position DROP FOREIGN KEY FK_50B65F6129BB6799');
        $this->addSql('ALTER TABLE order_source DROP FOREIGN KEY FK_9C056FA6E308AC6F');
        $this->addSql('ALTER TABLE consignment_item DROP FOREIGN KEY FK_53C7CD07E308AC6F');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25E308AC6F');
        $this->addSql('ALTER TABLE material_location DROP FOREIGN KEY FK_1846AD69E308AC6F');
        $this->addSql('ALTER TABLE material_order_position DROP FOREIGN KEY FK_50B65F61CB47499A');
        $this->addSql('ALTER TABLE consignment_item DROP FOREIGN KEY FK_53C7CD078F7B22CC');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB258F7B22CC');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595B6C0CF1');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED1B6C0CF1');
        $this->addSql('ALTER TABLE keyy DROP FOREIGN KEY FK_545BADF2B6C0CF1');
        $this->addSql('ALTER TABLE consignment_item DROP FOREIGN KEY FK_53C7CD0797B177ED');
        $this->addSql('ALTER TABLE consignment DROP FOREIGN KEY FK_32B8F88CA76ED395');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25602AD315');
        $this->addSql('ALTER TABLE stock_change DROP FOREIGN KEY FK_8A712CB6A76ED395');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBA76ED395');
        $this->addSql('ALTER TABLE supplier DROP FOREIGN KEY FK_9B2A6C7ED9DC8CA7');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE9395C3F3');
        $this->addSql('ALTER TABLE consignment DROP FOREIGN KEY FK_32B8F88C166D1F9C');
        $this->addSql('ALTER TABLE stock_change DROP FOREIGN KEY FK_8A712CB6166D1F9C');
        $this->addSql('ALTER TABLE supplier DROP FOREIGN KEY FK_9B2A6C7E979B1AD6');
        $this->addSql('ALTER TABLE order_source DROP FOREIGN KEY FK_9C056FA6979B1AD6');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595979B1AD6');
        $this->addSql('ALTER TABLE material_order DROP FOREIGN KEY FK_A3CE3FD6979B1AD6');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED1979B1AD6');
        $this->addSql('ALTER TABLE permission_group DROP FOREIGN KEY FK_BB4729B6979B1AD6');
        $this->addSql('ALTER TABLE consignment DROP FOREIGN KEY FK_32B8F88C979B1AD6');
        $this->addSql('ALTER TABLE material_order_position DROP FOREIGN KEY FK_50B65F61979B1AD6');
        $this->addSql('ALTER TABLE consignment_item DROP FOREIGN KEY FK_53C7CD07979B1AD6');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649979B1AD6');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25979B1AD6');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09979B1AD6');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE979B1AD6');
        $this->addSql('ALTER TABLE stock_change DROP FOREIGN KEY FK_8A712CB6979B1AD6');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB979B1AD6');
        $this->addSql('ALTER TABLE material_location DROP FOREIGN KEY FK_1846AD69979B1AD6');
        $this->addSql('ALTER TABLE item_group DROP FOREIGN KEY FK_47675F15979B1AD6');
        $this->addSql('ALTER TABLE custom_field DROP FOREIGN KEY FK_98F8BD31979B1AD6');
        $this->addSql('ALTER TABLE keyy DROP FOREIGN KEY FK_545BADF2979B1AD6');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED128CDC89C');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED17E3C61F9');
        $this->addSql('ALTER TABLE consignment DROP FOREIGN KEY FK_32B8F88C64D218E');
        $this->addSql('ALTER TABLE material_location DROP FOREIGN KEY FK_1846AD6964D218E');
        $this->addSql('ALTER TABLE keyy DROP FOREIGN KEY FK_545BADF228CDC89C');
        $this->addSql('ALTER TABLE keyy DROP FOREIGN KEY FK_545BADF27E3C61F9');
        $this->addSql('ALTER TABLE stock_change DROP FOREIGN KEY FK_8A712CB6A5511386');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE75959259118C');
        $this->addSql('ALTER TABLE tool DROP FOREIGN KEY FK_20F33ED19259118C');
        $this->addSql('ALTER TABLE consignment_item DROP FOREIGN KEY FK_53C7CD0737112802');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2537112802');
        $this->addSql('DROP TABLE supplier');
        $this->addSql('DROP TABLE order_source');
        $this->addSql('DROP TABLE material');
        $this->addSql('DROP TABLE material_order');
        $this->addSql('DROP TABLE tool');
        $this->addSql('DROP TABLE permission_group');
        $this->addSql('DROP TABLE consignment');
        $this->addSql('DROP TABLE material_order_position');
        $this->addSql('DROP TABLE consignment_item');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE connected_supplier');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE stock_change');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE material_location');
        $this->addSql('DROP TABLE item_group');
        $this->addSql('DROP TABLE custom_field');
        $this->addSql('DROP TABLE change_log');
        $this->addSql('DROP TABLE keyy');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
