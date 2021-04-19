<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201205205359 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595E22AB20A');
        $this->addSql('DROP INDEX IDX_7CBE7595E22AB20A ON material');
        $this->addSql('ALTER TABLE material ADD order_status_change_user_on_its_way_id VARCHAR(255) DEFAULT NULL, ADD order_status_change_user_available_id VARCHAR(255) DEFAULT NULL, CHANGE order_status_change_user_id order_status_change_user_to_order_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595F61DD7C FOREIGN KEY (order_status_change_user_to_order_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75953E28A4ED FOREIGN KEY (order_status_change_user_on_its_way_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595764357EA FOREIGN KEY (order_status_change_user_available_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7CBE7595F61DD7C ON material (order_status_change_user_to_order_id)');
        $this->addSql('CREATE INDEX IDX_7CBE75953E28A4ED ON material (order_status_change_user_on_its_way_id)');
        $this->addSql('CREATE INDEX IDX_7CBE7595764357EA ON material (order_status_change_user_available_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595F61DD7C');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE75953E28A4ED');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595764357EA');
        $this->addSql('DROP INDEX IDX_7CBE7595F61DD7C ON material');
        $this->addSql('DROP INDEX IDX_7CBE75953E28A4ED ON material');
        $this->addSql('DROP INDEX IDX_7CBE7595764357EA ON material');
        $this->addSql('ALTER TABLE material ADD order_status_change_user_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP order_status_change_user_to_order_id, DROP order_status_change_user_on_its_way_id, DROP order_status_change_user_available_id');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595E22AB20A FOREIGN KEY (order_status_change_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7CBE7595E22AB20A ON material (order_status_change_user_id)');
    }
}
