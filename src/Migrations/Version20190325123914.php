<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190325123914 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment_transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sold_product_id INT NOT NULL, method VARCHAR(255) NOT NULL, transaction_id VARCHAR(255) NOT NULL, chosen_at DATETIME NOT NULL, paid_at DATETIME DEFAULT NULL, confirmed TINYINT(1) NOT NULL, INDEX IDX_84BBD50BA76ED395 (user_id), INDEX IDX_84BBD50BE8EE9BF1 (sold_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment_transaction ADD CONSTRAINT FK_84BBD50BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment_transaction ADD CONSTRAINT FK_84BBD50BE8EE9BF1 FOREIGN KEY (sold_product_id) REFERENCES sold (id)');
        $this->addSql('DROP TABLE on_delivery_transaction');
        $this->addSql('DROP TABLE paypal_transaction');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE on_delivery_transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sold_product_id INT NOT NULL, chosen_at DATETIME NOT NULL, confirmed TINYINT(1) NOT NULL, file_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_3803C8C9A76ED395 (user_id), INDEX IDX_3803C8C9E8EE9BF1 (sold_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE paypal_transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sold_product_id INT NOT NULL, transaction_id VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, paid_at DATETIME NOT NULL, confirmed TINYINT(1) NOT NULL, INDEX IDX_8CB5DC99A76ED395 (user_id), INDEX IDX_8CB5DC99E8EE9BF1 (sold_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE on_delivery_transaction ADD CONSTRAINT FK_3803C8C9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE on_delivery_transaction ADD CONSTRAINT FK_3803C8C9E8EE9BF1 FOREIGN KEY (sold_product_id) REFERENCES sold (id)');
        $this->addSql('ALTER TABLE paypal_transaction ADD CONSTRAINT FK_8CB5DC99A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE paypal_transaction ADD CONSTRAINT FK_8CB5DC99E8EE9BF1 FOREIGN KEY (sold_product_id) REFERENCES sold (id)');
        $this->addSql('DROP TABLE payment_transaction');
    }
}
