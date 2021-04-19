<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212200422 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE direct_order CHANGE crawlerstatus_value autoStatus_value VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE direct_order_position_result CHANGE crawlerstatus_value autoStatus_value VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595D21A2081');
        $this->addSql('DROP INDEX IDX_7CBE7595D21A2081 ON material');
        $this->addSql('ALTER TABLE material ADD auto_supplier_id VARCHAR(255) DEFAULT NULL, ADD auto_search_term VARCHAR(255) DEFAULT NULL, DROP crawler_supplier_id, DROP crawler_search_term, CHANGE crawlerstatus_value autoStatus_value VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75959BBC194C FOREIGN KEY (auto_supplier_id) REFERENCES supplier (id)');
        $this->addSql('CREATE INDEX IDX_7CBE75959BBC194C ON material (auto_supplier_id)');
        $this->addSql('ALTER TABLE order_source CHANGE last_crawler_set last_auto_set DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE crawlerstatus_value autoStatus_value VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE direct_order CHANGE autostatus_value crawlerStatus_value VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE direct_order_position_result CHANGE autostatus_value crawlerStatus_value VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE75959BBC194C');
        $this->addSql('DROP INDEX IDX_7CBE75959BBC194C ON material');
        $this->addSql('ALTER TABLE material ADD crawler_supplier_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD crawler_search_term VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP auto_supplier_id, DROP auto_search_term, CHANGE autostatus_value crawlerStatus_value VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595D21A2081 FOREIGN KEY (crawler_supplier_id) REFERENCES supplier (id)');
        $this->addSql('CREATE INDEX IDX_7CBE7595D21A2081 ON material (crawler_supplier_id)');
        $this->addSql('ALTER TABLE order_source CHANGE last_auto_set last_crawler_set DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE autostatus_value crawlerStatus_value VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
