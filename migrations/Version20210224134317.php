<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210224134317 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE label_type DROP FOREIGN KEY FK_40E6C2E548B7D5E9');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491750F197');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493FF3A000');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498C0C4241');
        $this->addSql('CREATE TABLE pdf_document_type (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, pdf_specification_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', pdf_item_fields JSON NOT NULL, name VARCHAR(255) NOT NULL, entityType_value VARCHAR(10) NOT NULL, INDEX IDX_756E8D8C979B1AD6 (company_id), INDEX IDX_756E8D8C5BE48849 (pdf_specification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pdf_specification (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', page_size VARCHAR(255) NOT NULL, orientation VARCHAR(255) NOT NULL, label_width DOUBLE PRECISION NOT NULL, label_height DOUBLE PRECISION NOT NULL, print_header TINYINT(1) NOT NULL, print_footer TINYINT(1) NOT NULL, label_start_x DOUBLE PRECISION NOT NULL, label_start_y DOUBLE PRECISION NOT NULL, labels_per_column INT NOT NULL, rows_per_page INT NOT NULL, name VARCHAR(255) NOT NULL, field_dimensions JSON NOT NULL, field_count INT NOT NULL, default_field_types_material LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', default_field_types_tool LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', default_field_types_keyy LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', default_field_properties_material LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', default_field_properties_tool LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', default_field_properties_keyy LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', INDEX IDX_503B4CAC979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pdf_document_type ADD CONSTRAINT FK_756E8D8C979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE pdf_document_type ADD CONSTRAINT FK_756E8D8C5BE48849 FOREIGN KEY (pdf_specification_id) REFERENCES pdf_specification (id)');
        $this->addSql('ALTER TABLE pdf_specification ADD CONSTRAINT FK_503B4CAC979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('DROP TABLE label_specification');
        $this->addSql('DROP TABLE label_type');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491750F197 FOREIGN KEY (selected_tool_label_type_id) REFERENCES pdf_document_type (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493FF3A000 FOREIGN KEY (selected_keyy_label_type_id) REFERENCES pdf_document_type (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498C0C4241 FOREIGN KEY (selected_material_label_type_id) REFERENCES pdf_document_type (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498C0C4241');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491750F197');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493FF3A000');
        $this->addSql('ALTER TABLE pdf_document_type DROP FOREIGN KEY FK_756E8D8C5BE48849');
        $this->addSql('CREATE TABLE label_specification (id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, company_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', page_size VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, orientation VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label_width DOUBLE PRECISION NOT NULL, label_height DOUBLE PRECISION NOT NULL, print_header TINYINT(1) NOT NULL, print_footer TINYINT(1) NOT NULL, label_start_x DOUBLE PRECISION NOT NULL, label_start_y DOUBLE PRECISION NOT NULL, labels_per_column INT NOT NULL, rows_per_page INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, field_dimensions JSON NOT NULL, field_count INT NOT NULL, default_field_types_material LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', default_field_types_tool LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', default_field_types_keyy LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', default_field_properties_material LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', default_field_properties_tool LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', default_field_properties_keyy LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', INDEX IDX_CA0A65A9979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE label_type (id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, company_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, label_specification_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', label_fields JSON NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, entityType_value VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_40E6C2E548B7D5E9 (label_specification_id), INDEX IDX_40E6C2E5979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE label_specification ADD CONSTRAINT FK_CA0A65A9979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE label_type ADD CONSTRAINT FK_40E6C2E548B7D5E9 FOREIGN KEY (label_specification_id) REFERENCES label_specification (id)');
        $this->addSql('ALTER TABLE label_type ADD CONSTRAINT FK_40E6C2E5979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('DROP TABLE pdf_document_type');
        $this->addSql('DROP TABLE pdf_specification');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498C0C4241');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491750F197');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493FF3A000');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498C0C4241 FOREIGN KEY (selected_material_label_type_id) REFERENCES label_type (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491750F197 FOREIGN KEY (selected_tool_label_type_id) REFERENCES label_type (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493FF3A000 FOREIGN KEY (selected_keyy_label_type_id) REFERENCES label_type (id)');
    }
}
