<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210225180141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pdf_document_type ADD common_fields JSON NOT NULL, CHANGE pdf_item_fields item_fields JSON NOT NULL');
        $this->addSql('ALTER TABLE pdf_specification ADD common_field_dimensions JSON NOT NULL, ADD common_fields_count INT NOT NULL, CHANGE field_dimensions item_field_dimensions JSON NOT NULL, CHANGE field_count item_fields_count INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pdf_document_type ADD pdf_item_fields JSON NOT NULL, DROP item_fields, DROP common_fields');
        $this->addSql('ALTER TABLE pdf_specification ADD field_dimensions JSON NOT NULL, ADD field_count INT NOT NULL, DROP item_field_dimensions, DROP common_field_dimensions, DROP item_fields_count, DROP common_fields_count');
    }
}
