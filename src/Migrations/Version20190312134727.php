<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190312134727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ab_category_2_product DROP FOREIGN KEY FK_C17D327F12469DE2');
        $this->addSql('ALTER TABLE ab_category_2_product DROP FOREIGN KEY FK_C17D327F4584665A');
        $this->addSql('ALTER TABLE ab_category_2_product DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE ab_category_2_product ADD CONSTRAINT FK_C17D327F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE ab_category_2_product ADD CONSTRAINT FK_C17D327F4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE ab_category_2_product ADD PRIMARY KEY (category_id, product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ab_category_2_product DROP FOREIGN KEY FK_C17D327F12469DE2');
        $this->addSql('ALTER TABLE ab_category_2_product DROP FOREIGN KEY FK_C17D327F4584665A');
        $this->addSql('ALTER TABLE ab_category_2_product DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE ab_category_2_product ADD CONSTRAINT FK_C17D327F12469DE2 FOREIGN KEY (category_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE ab_category_2_product ADD CONSTRAINT FK_C17D327F4584665A FOREIGN KEY (product_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE ab_category_2_product ADD PRIMARY KEY (product_id, category_id)');
    }
}
