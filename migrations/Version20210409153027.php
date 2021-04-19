<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210409153027 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pdf_specification');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pdf_specification (id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, company_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', page_size VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, orientation VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label_width DOUBLE PRECISION NOT NULL, label_height DOUBLE PRECISION NOT NULL, print_header TINYINT(1) NOT NULL, print_footer TINYINT(1) NOT NULL, label_start_x DOUBLE PRECISION NOT NULL, label_start_y DOUBLE PRECISION NOT NULL, labels_per_column INT NOT NULL, rows_per_page INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, item_field_dimensions JSON NOT NULL, item_fields_count INT NOT NULL, default_field_types_material JSON DEFAULT NULL, default_field_types_tool JSON DEFAULT NULL, default_field_types_keyy JSON DEFAULT NULL, default_field_properties_material JSON DEFAULT NULL, default_field_properties_tool JSON DEFAULT NULL, default_field_properties_keyy JSON DEFAULT NULL, pdfSpecificationType_value VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, common_field_dimensions JSON NOT NULL, common_fields_count INT NOT NULL, default_common_field_types JSON DEFAULT NULL, default_common_field_properties JSON DEFAULT NULL, copies INT NOT NULL, background_image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, default_common_field_params JSON DEFAULT NULL, INDEX IDX_503B4CAC979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE pdf_specification ADD CONSTRAINT FK_503B4CAC979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }
}
