<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210307052358 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE price_update (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, order_source_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount_per_purchase_unit DOUBLE PRECISION NOT NULL, price VARCHAR(255) COMMENT \'(DC2Type:money)\' NOT NULL, source VARCHAR(255) DEFAULT NULL, INDEX IDX_D5BC9D28979B1AD6 (company_id), INDEX IDX_D5BC9D2829BB6799 (order_source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE price_update ADD CONSTRAINT FK_D5BC9D28979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE price_update ADD CONSTRAINT FK_D5BC9D2829BB6799 FOREIGN KEY (order_source_id) REFERENCES order_source (id)');
        $this->addSql('ALTER TABLE consignment_item ADD manual_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_source CHANGE amount_per_purchase_unit amount_per_purchase_unit DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE price_update');
        $this->addSql('ALTER TABLE consignment_item DROP manual_name');
        $this->addSql('ALTER TABLE order_source CHANGE amount_per_purchase_unit amount_per_purchase_unit VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
