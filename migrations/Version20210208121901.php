<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210208121901 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE direct_order_position ADD material_order_position_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE direct_order_position ADD CONSTRAINT FK_6F7111B830753355 FOREIGN KEY (material_order_position_id) REFERENCES material_order_position (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6F7111B830753355 ON direct_order_position (material_order_position_id)');
        $this->addSql('ALTER TABLE material_order_position DROP FOREIGN KEY FK_50B65F61D9D7084D');
        $this->addSql('DROP INDEX UNIQ_50B65F61D9D7084D ON material_order_position');
        $this->addSql('ALTER TABLE material_order_position DROP direct_order_position_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE direct_order_position DROP FOREIGN KEY FK_6F7111B830753355');
        $this->addSql('DROP INDEX UNIQ_6F7111B830753355 ON direct_order_position');
        $this->addSql('ALTER TABLE direct_order_position DROP material_order_position_id');
        $this->addSql('ALTER TABLE material_order_position ADD direct_order_position_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE material_order_position ADD CONSTRAINT FK_50B65F61D9D7084D FOREIGN KEY (direct_order_position_id) REFERENCES direct_order_position (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_50B65F61D9D7084D ON material_order_position (direct_order_position_id)');
    }
}
