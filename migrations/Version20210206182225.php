<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210206182225 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE direct_order_position (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, direct_order_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', order_number VARCHAR(255) NOT NULL, amount INT NOT NULL, INDEX IDX_6F7111B8979B1AD6 (company_id), INDEX IDX_6F7111B8248168F4 (direct_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE direct_order_position_result (id VARCHAR(255) NOT NULL, company_id VARCHAR(255) DEFAULT NULL, direct_order_position_id VARCHAR(255) DEFAULT NULL, supplier_id VARCHAR(255) DEFAULT NULL, order_source_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', avilability VARCHAR(255) DEFAULT NULL, materialOrderStatus_value VARCHAR(10) DEFAULT NULL, INDEX IDX_96BED843979B1AD6 (company_id), INDEX IDX_96BED843D9D7084D (direct_order_position_id), INDEX IDX_96BED8432ADD6D8C (supplier_id), INDEX IDX_96BED84329BB6799 (order_source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE direct_order_position ADD CONSTRAINT FK_6F7111B8979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE direct_order_position ADD CONSTRAINT FK_6F7111B8248168F4 FOREIGN KEY (direct_order_id) REFERENCES direct_order (id)');
        $this->addSql('ALTER TABLE direct_order_position_result ADD CONSTRAINT FK_96BED843979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE direct_order_position_result ADD CONSTRAINT FK_96BED843D9D7084D FOREIGN KEY (direct_order_position_id) REFERENCES direct_order_position (id)');
        $this->addSql('ALTER TABLE direct_order_position_result ADD CONSTRAINT FK_96BED8432ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE direct_order_position_result ADD CONSTRAINT FK_96BED84329BB6799 FOREIGN KEY (order_source_id) REFERENCES order_source (id)');
        $this->addSql('ALTER TABLE direct_order ADD main_supplier_id VARCHAR(255) DEFAULT NULL, ADD direct_order_number INT NOT NULL, DROP direct_order_request, DROP direct_order_result');
        $this->addSql('ALTER TABLE direct_order ADD CONSTRAINT FK_17C0252CFE5EA51A FOREIGN KEY (main_supplier_id) REFERENCES supplier (id)');
        $this->addSql('CREATE INDEX IDX_17C0252CFE5EA51A ON direct_order (main_supplier_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE direct_order_position_result DROP FOREIGN KEY FK_96BED843D9D7084D');
        $this->addSql('DROP TABLE direct_order_position');
        $this->addSql('DROP TABLE direct_order_position_result');
        $this->addSql('ALTER TABLE direct_order DROP FOREIGN KEY FK_17C0252CFE5EA51A');
        $this->addSql('DROP INDEX IDX_17C0252CFE5EA51A ON direct_order');
        $this->addSql('ALTER TABLE direct_order ADD direct_order_request JSON NOT NULL, ADD direct_order_result JSON DEFAULT NULL, DROP main_supplier_id, DROP direct_order_number');
    }
}
