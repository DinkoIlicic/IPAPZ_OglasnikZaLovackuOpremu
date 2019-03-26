<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190326103201 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE seller CHANGE verified verified TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE visibility visibility TINYINT(1) NOT NULL, CHANGE visibility_admin visibility_admin TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE sold CHANGE confirmed confirmed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE visibility visibility TINYINT(1) NOT NULL, CHANGE visibility_admin visibility_admin TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE custom_page CHANGE visibility_admin visibility_admin TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE wishlist CHANGE notify notify TINYINT(1) NOT NULL, CHANGE notified notified TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category CHANGE visibility visibility INT NOT NULL, CHANGE visibility_admin visibility_admin INT NOT NULL');
        $this->addSql('ALTER TABLE custom_page CHANGE visibility_admin visibility_admin INT NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE visibility visibility INT NOT NULL, CHANGE visibility_admin visibility_admin INT NOT NULL');
        $this->addSql('ALTER TABLE seller CHANGE verified verified INT NOT NULL');
        $this->addSql('ALTER TABLE sold CHANGE confirmed confirmed INT NOT NULL');
        $this->addSql('ALTER TABLE wishlist CHANGE notify notify SMALLINT NOT NULL, CHANGE notified notified SMALLINT NOT NULL');
    }
}
