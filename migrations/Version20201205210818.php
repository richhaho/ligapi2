<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201205210818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material ADD order_status_change_date_on_its_way DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD order_status_change_date_available DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE order_status_change_date order_status_change_date_to_order DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material ADD order_status_change_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP order_status_change_date_to_order, DROP order_status_change_date_on_its_way, DROP order_status_change_date_available');
    }
}
