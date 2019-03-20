<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190320111434 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE coupon_codes (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, product_id INT NOT NULL, code_group_id INT NOT NULL, code_name VARCHAR(255) NOT NULL, discount VARCHAR(255) NOT NULL, `all` INT NOT NULL, date_enabled INT NOT NULL, start_date DATETIME NOT NULL, expire_data DATETIME NOT NULL, UNIQUE INDEX UNIQ_BE39EBD68C814C7 (code_name), INDEX IDX_BE39EBD12469DE2 (category_id), INDEX IDX_BE39EBD4584665A (product_id), INDEX IDX_BE39EBD7D5A16C (code_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coupon (id INT AUTO_INCREMENT NOT NULL, code_group_name VARCHAR(255) NOT NULL, discount VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_64BF3F025FD7D890 (code_group_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coupon_codes ADD CONSTRAINT FK_BE39EBD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE coupon_codes ADD CONSTRAINT FK_BE39EBD4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE coupon_codes ADD CONSTRAINT FK_BE39EBD7D5A16C FOREIGN KEY (code_group_id) REFERENCES coupon (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE coupon_codes DROP FOREIGN KEY FK_BE39EBD7D5A16C');
        $this->addSql('DROP TABLE coupon_codes');
        $this->addSql('DROP TABLE coupon');
    }
}
