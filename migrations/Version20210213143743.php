<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210213143743 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE label_specification ADD default_field_types_material LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', ADD default_field_types_tool LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', ADD default_field_types_keyy LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', ADD default_field_properties_material LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', ADD default_field_properties_tool LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', ADD default_field_properties_keyy LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', DROP default_field_types, DROP default_field_properties');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE label_specification ADD default_field_types LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD default_field_properties LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', DROP default_field_types_material, DROP default_field_types_tool, DROP default_field_types_keyy, DROP default_field_properties_material, DROP default_field_properties_tool, DROP default_field_properties_keyy');
    }
}
