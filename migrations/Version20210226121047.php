<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210226121047 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pdf_specification CHANGE default_field_types_material default_field_types_material LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_types_tool default_field_types_tool LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_types_keyy default_field_types_keyy LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_material default_field_properties_material LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_tool default_field_properties_tool LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_keyy default_field_properties_keyy LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pdf_specification CHANGE default_field_types_material default_field_types_material LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_types_tool default_field_types_tool LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_types_keyy default_field_types_keyy LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_material default_field_properties_material LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_tool default_field_properties_tool LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', CHANGE default_field_properties_keyy default_field_properties_keyy LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\'');
    }
}
