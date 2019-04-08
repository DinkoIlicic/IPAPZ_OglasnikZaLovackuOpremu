<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190408084417 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE custom_page (id INT AUTO_INCREMENT NOT NULL, page_name VARCHAR(255) NOT NULL, custom_url VARCHAR(255) NOT NULL, content VARCHAR(2000) NOT NULL, visibility_admin TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, country VARCHAR(255) NOT NULL, address_first VARCHAR(255) NOT NULL, address_second VARCHAR(255) DEFAULT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, postal_code INT NOT NULL, INDEX IDX_5543718BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seller (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, apply_content VARCHAR(255) NOT NULL, verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_FB1AD3FCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_category (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, category_id INT DEFAULT NULL, INDEX IDX_CDFC73564584665A (product_id), INDEX IDX_CDFC735612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coupon (id INT AUTO_INCREMENT NOT NULL, code_group_name VARCHAR(255) NOT NULL, discount VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_64BF3F025FD7D890 (code_group_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sold_product_id INT NOT NULL, user_address_id INT NOT NULL, method VARCHAR(255) NOT NULL, transaction_id VARCHAR(255) DEFAULT NULL, chosen_at DATETIME NOT NULL, paid_at DATETIME DEFAULT NULL, confirmed TINYINT(1) NOT NULL, INDEX IDX_84BBD50BA76ED395 (user_id), INDEX IDX_84BBD50BE8EE9BF1 (sold_product_id), INDEX IDX_84BBD50B52D06999 (user_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_method (id INT AUTO_INCREMENT NOT NULL, method LONGTEXT NOT NULL, enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sold (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT NOT NULL, address_id INT DEFAULT NULL, quantity INT NOT NULL, bought_at DATETIME NOT NULL, price NUMERIC(10, 2) NOT NULL, coupon_code_name VARCHAR(255) NOT NULL, discount VARCHAR(255) NOT NULL, total_price NUMERIC(10, 2) NOT NULL, confirmed TINYINT(1) NOT NULL, after_discount NUMERIC(10, 2) NOT NULL, payment_method VARCHAR(255) DEFAULT NULL, shipping_price NUMERIC(10, 2) NOT NULL, to_pay NUMERIC(10, 2) NOT NULL, INDEX IDX_98D2DD99A76ED395 (user_id), INDEX IDX_98D2DD994584665A (product_id), INDEX IDX_98D2DD99F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, user_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9474526C4584665A (product_id), INDEX IDX_9474526CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, url_name VARCHAR(255) NOT NULL, visibility_admin TINYINT(1) NOT NULL, INDEX IDX_64C19C1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wishlist (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, user_id INT DEFAULT NULL, notify TINYINT(1) NOT NULL, notified TINYINT(1) NOT NULL, INDEX IDX_9CE12A314584665A (product_id), INDEX IDX_9CE12A31A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, content VARCHAR(255) NOT NULL, visibility TINYINT(1) NOT NULL, visibility_admin TINYINT(1) NOT NULL, image VARCHAR(255) NOT NULL, available_quantity INT NOT NULL, custom_url VARCHAR(255) NOT NULL, INDEX IDX_D34A04ADA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coupon_codes (id INT AUTO_INCREMENT NOT NULL, code_group_id INT NOT NULL, code_name VARCHAR(255) NOT NULL, discount VARCHAR(255) NOT NULL, all_products INT NOT NULL, category_id INT NOT NULL, product_id INT NOT NULL, UNIQUE INDEX UNIQ_BE39EBD68C814C7 (code_name), INDEX IDX_BE39EBD7D5A16C (code_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shipping (country VARCHAR(255) NOT NULL, country_code VARCHAR(255) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, UNIQUE INDEX UNIQ_2D1C1724F026BB7C (country_code), PRIMARY KEY(country)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE seller ADD CONSTRAINT FK_FB1AD3FCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73564584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC735612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE payment_transaction ADD CONSTRAINT FK_84BBD50BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment_transaction ADD CONSTRAINT FK_84BBD50BE8EE9BF1 FOREIGN KEY (sold_product_id) REFERENCES sold (id)');
        $this->addSql('ALTER TABLE payment_transaction ADD CONSTRAINT FK_84BBD50B52D06999 FOREIGN KEY (user_address_id) REFERENCES user_address (id)');
        $this->addSql('ALTER TABLE sold ADD CONSTRAINT FK_98D2DD99A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sold ADD CONSTRAINT FK_98D2DD994584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE sold ADD CONSTRAINT FK_98D2DD99F5B7AF75 FOREIGN KEY (address_id) REFERENCES user_address (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A314584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A31A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE coupon_codes ADD CONSTRAINT FK_BE39EBD7D5A16C FOREIGN KEY (code_group_id) REFERENCES coupon (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_address DROP FOREIGN KEY FK_5543718BA76ED395');
        $this->addSql('ALTER TABLE seller DROP FOREIGN KEY FK_FB1AD3FCA76ED395');
        $this->addSql('ALTER TABLE payment_transaction DROP FOREIGN KEY FK_84BBD50BA76ED395');
        $this->addSql('ALTER TABLE sold DROP FOREIGN KEY FK_98D2DD99A76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1A76ED395');
        $this->addSql('ALTER TABLE wishlist DROP FOREIGN KEY FK_9CE12A31A76ED395');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA76ED395');
        $this->addSql('ALTER TABLE payment_transaction DROP FOREIGN KEY FK_84BBD50B52D06999');
        $this->addSql('ALTER TABLE sold DROP FOREIGN KEY FK_98D2DD99F5B7AF75');
        $this->addSql('ALTER TABLE coupon_codes DROP FOREIGN KEY FK_BE39EBD7D5A16C');
        $this->addSql('ALTER TABLE payment_transaction DROP FOREIGN KEY FK_84BBD50BE8EE9BF1');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC735612469DE2');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC73564584665A');
        $this->addSql('ALTER TABLE sold DROP FOREIGN KEY FK_98D2DD994584665A');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4584665A');
        $this->addSql('ALTER TABLE wishlist DROP FOREIGN KEY FK_9CE12A314584665A');
        $this->addSql('DROP TABLE custom_page');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_address');
        $this->addSql('DROP TABLE seller');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE payment_transaction');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE sold');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE wishlist');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE coupon_codes');
        $this->addSql('DROP TABLE shipping');
    }
}
