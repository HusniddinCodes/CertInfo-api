<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240607122322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create SecretKey table for reset password';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE secret_key (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, secret_key VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_7F4741F5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE secret_key ADD CONSTRAINT FK_7F4741F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE secret_key DROP FOREIGN KEY FK_7F4741F5A76ED395');
        $this->addSql('DROP TABLE secret_key');
    }
}
