<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190221073125 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sold ADD user_id INT NOT NULL, ADD product_id INT NOT NULL, ADD quantity INT NOT NULL');
        $this->addSql('ALTER TABLE sold ADD CONSTRAINT FK_98D2DD99A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sold ADD CONSTRAINT FK_98D2DD994584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_98D2DD99A76ED395 ON sold (user_id)');
        $this->addSql('CREATE INDEX IDX_98D2DD994584665A ON sold (product_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sold DROP FOREIGN KEY FK_98D2DD99A76ED395');
        $this->addSql('ALTER TABLE sold DROP FOREIGN KEY FK_98D2DD994584665A');
        $this->addSql('DROP INDEX IDX_98D2DD99A76ED395 ON sold');
        $this->addSql('DROP INDEX IDX_98D2DD994584665A ON sold');
        $this->addSql('ALTER TABLE sold DROP user_id, DROP product_id, DROP quantity');
    }
}
