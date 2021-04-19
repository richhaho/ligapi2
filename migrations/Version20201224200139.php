<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201224200139 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material DROP INDEX IDX_7CBE75955E841800, ADD UNIQUE INDEX UNIQ_7CBE75955E841800 (last_material_order_position_id)');
        $this->addSql('ALTER TABLE material_order_position ADD price DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material DROP INDEX UNIQ_7CBE75955E841800, ADD INDEX IDX_7CBE75955E841800 (last_material_order_position_id)');
        $this->addSql('ALTER TABLE material_order_position DROP price');
    }
}
