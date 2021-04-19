<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210317113442 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consignment ADD delivery_address VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE pdf_specification CHANGE default_field_types_material default_field_types_material JSON DEFAULT NULL, CHANGE default_field_types_tool default_field_types_tool JSON DEFAULT NULL, CHANGE default_field_types_keyy default_field_types_keyy JSON DEFAULT NULL, CHANGE default_field_properties_material default_field_properties_material JSON DEFAULT NULL, CHANGE default_field_properties_tool default_field_properties_tool JSON DEFAULT NULL, CHANGE default_field_properties_keyy default_field_properties_keyy JSON DEFAULT NULL, CHANGE default_common_field_types default_common_field_types JSON DEFAULT NULL, CHANGE default_common_field_properties default_common_field_properties JSON DEFAULT NULL, CHANGE default_common_field_params default_common_field_params JSON DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE consignment DROP delivery_address');
        $this->addSql('ALTER TABLE pdf_specification CHANGE default_common_field_types default_common_field_types LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_common_field_params default_common_field_params LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_types_material default_field_types_material LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_types_tool default_field_types_tool LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_types_keyy default_field_types_keyy LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_common_field_properties default_common_field_properties LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_material default_field_properties_material LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_tool default_field_properties_tool LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_keyy default_field_properties_keyy LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\'');
    }
}
