<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190327140127 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sold ADD address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sold ADD CONSTRAINT FK_98D2DD99F5B7AF75 FOREIGN KEY (address_id) REFERENCES user_address (id)');
        $this->addSql('CREATE INDEX IDX_98D2DD99F5B7AF75 ON sold (address_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sold DROP FOREIGN KEY FK_98D2DD99F5B7AF75');
        $this->addSql('DROP INDEX IDX_98D2DD99F5B7AF75 ON sold');
        $this->addSql('ALTER TABLE sold DROP address_id');
    }
}
