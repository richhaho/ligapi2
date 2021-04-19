<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210124192738 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE label_specification (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', page_size VARCHAR(255) NOT NULL, orientation VARCHAR(255) NOT NULL, label_width DOUBLE PRECISION NOT NULL, label_height DOUBLE PRECISION NOT NULL, print_header TINYINT(1) NOT NULL, print_footer TINYINT(1) NOT NULL, label_start_x DOUBLE PRECISION NOT NULL, label_start_y DOUBLE PRECISION NOT NULL, labels_per_column INT NOT NULL, rows_per_page INT NOT NULL, name VARCHAR(255) NOT NULL, field_dimensions JSON NOT NULL, default_field_types LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', INDEX IDX_CA0A65A9979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE label_specification ADD CONSTRAINT FK_CA0A65A9979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE label_type ADD label_specification_id VARCHAR(255) DEFAULT NULL, DROP label_specification');
        $this->addSql('ALTER TABLE label_type ADD CONSTRAINT FK_40E6C2E548B7D5E9 FOREIGN KEY (label_specification_id) REFERENCES label_specification (id)');
        $this->addSql('CREATE INDEX IDX_40E6C2E548B7D5E9 ON label_type (label_specification_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE label_type DROP FOREIGN KEY FK_40E6C2E548B7D5E9');
        $this->addSql('DROP TABLE label_specification');
        $this->addSql('DROP INDEX IDX_40E6C2E548B7D5E9 ON label_type');
        $this->addSql('ALTER TABLE label_type ADD label_specification VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP label_specification_id');
    }
}
