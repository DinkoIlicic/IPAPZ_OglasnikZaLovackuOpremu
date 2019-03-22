<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190322083648 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE paypal_transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sold_product_id INT NOT NULL, transaction_id VARCHAR(255) NOT NULL, paid_at DATETIME NOT NULL, confirmed TINYINT(1) NOT NULL, INDEX IDX_8CB5DC99A76ED395 (user_id), INDEX IDX_8CB5DC99E8EE9BF1 (sold_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE paypal_transaction ADD CONSTRAINT FK_8CB5DC99A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE paypal_transaction ADD CONSTRAINT FK_8CB5DC99E8EE9BF1 FOREIGN KEY (sold_product_id) REFERENCES sold (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE paypal_transaction');
    }
}
