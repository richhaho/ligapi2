<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210409064537 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pdf_document_type DROP FOREIGN KEY FK_756E8D8C5BE48849');
        $this->addSql('DROP INDEX IDX_756E8D8C5BE48849 ON pdf_document_type');
        $this->addSql('ALTER TABLE pdf_document_type CHANGE pdf_specification_id pdf_specification_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491750F197');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493FF3A000');
        $this->addSql('DROP INDEX IDX_8D93D6491750F197 ON user');
        $this->addSql('DROP INDEX IDX_8D93D6498C0C4241 ON user');
        $this->addSql('DROP INDEX IDX_8D93D6493FF3A000 ON user');
        $this->addSql('ALTER TABLE user DROP selected_tool_label_type_id, DROP selected_keyy_label_type_id, DROP selected_material_label_type_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pdf_document_type CHANGE pdf_specification_id pdf_specification_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE pdf_document_type ADD CONSTRAINT FK_756E8D8C5BE48849 FOREIGN KEY (pdf_specification_id) REFERENCES pdf_specification (id)');
        $this->addSql('CREATE INDEX IDX_756E8D8C5BE48849 ON pdf_document_type (pdf_specification_id)');
        $this->addSql('ALTER TABLE user ADD selected_tool_label_type_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD selected_keyy_label_type_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD selected_material_label_type_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491750F197 FOREIGN KEY (selected_tool_label_type_id) REFERENCES pdf_document_type (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493FF3A000 FOREIGN KEY (selected_keyy_label_type_id) REFERENCES pdf_document_type (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6491750F197 ON user (selected_tool_label_type_id)');
        $this->addSql('CREATE INDEX IDX_8D93D6498C0C4241 ON user (selected_material_label_type_id)');
        $this->addSql('CREATE INDEX IDX_8D93D6493FF3A000 ON user (selected_keyy_label_type_id)');
    }
}
