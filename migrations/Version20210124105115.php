<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210124105115 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD selected_material_label_type_id VARCHAR(255) DEFAULT NULL, ADD selected_tool_label_type_id VARCHAR(255) DEFAULT NULL, ADD selected_keyy_label_type_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498C0C4241 FOREIGN KEY (selected_material_label_type_id) REFERENCES label_type (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491750F197 FOREIGN KEY (selected_tool_label_type_id) REFERENCES label_type (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493FF3A000 FOREIGN KEY (selected_keyy_label_type_id) REFERENCES label_type (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6498C0C4241 ON user (selected_material_label_type_id)');
        $this->addSql('CREATE INDEX IDX_8D93D6491750F197 ON user (selected_tool_label_type_id)');
        $this->addSql('CREATE INDEX IDX_8D93D6493FF3A000 ON user (selected_keyy_label_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498C0C4241');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491750F197');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493FF3A000');
        $this->addSql('DROP INDEX IDX_8D93D6498C0C4241 ON user');
        $this->addSql('DROP INDEX IDX_8D93D6491750F197 ON user');
        $this->addSql('DROP INDEX IDX_8D93D6493FF3A000 ON user');
        $this->addSql('ALTER TABLE user DROP selected_material_label_type_id, DROP selected_tool_label_type_id, DROP selected_keyy_label_type_id');
    }
}
