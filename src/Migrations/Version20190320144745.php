<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190320144745 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE coupon_codes DROP FOREIGN KEY FK_BE39EBD12469DE2');
        $this->addSql('ALTER TABLE coupon_codes DROP FOREIGN KEY FK_BE39EBD4584665A');
        $this->addSql('DROP INDEX IDX_BE39EBD12469DE2 ON coupon_codes');
        $this->addSql('DROP INDEX IDX_BE39EBD4584665A ON coupon_codes');
        $this->addSql('ALTER TABLE coupon_codes ADD category INT NOT NULL, ADD product INT NOT NULL, DROP category_id, DROP product_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE coupon_codes ADD category_id INT NOT NULL, ADD product_id INT NOT NULL, DROP category, DROP product');
        $this->addSql('ALTER TABLE coupon_codes ADD CONSTRAINT FK_BE39EBD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE coupon_codes ADD CONSTRAINT FK_BE39EBD4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_BE39EBD12469DE2 ON coupon_codes (category_id)');
        $this->addSql('CREATE INDEX IDX_BE39EBD4584665A ON coupon_codes (product_id)');
    }
}
