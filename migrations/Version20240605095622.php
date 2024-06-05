<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240605095622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add imgCertificate column to Certificate table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate ADD img_certificate_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A4FB8FF8B FOREIGN KEY (img_certificate_id) REFERENCES media_object (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_219CDA4A4FB8FF8B ON certificate (img_certificate_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A4FB8FF8B');
        $this->addSql('DROP INDEX UNIQ_219CDA4A4FB8FF8B ON certificate');
        $this->addSql('ALTER TABLE certificate DROP img_certificate_id');
    }
}
