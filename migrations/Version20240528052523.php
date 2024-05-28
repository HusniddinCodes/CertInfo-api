<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240528052523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create Certificate table and change person table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, file_id INT DEFAULT NULL, course_id INT NOT NULL, created_by_id INT NOT NULL, updated_by_id INT DEFAULT NULL, deleted_by_id INT DEFAULT NULL, course_finished_date DATE NOT NULL, practice_description VARCHAR(255) DEFAULT NULL, certificate_defense VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_219CDA4A7E3C61F9 (owner_id), INDEX IDX_219CDA4A93CB796C (file_id), INDEX IDX_219CDA4A591CC992 (course_id), INDEX IDX_219CDA4AB03A8386 (created_by_id), INDEX IDX_219CDA4A896DBBDE (updated_by_id), INDEX IDX_219CDA4AC76F1F52 (deleted_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A93CB796C FOREIGN KEY (file_id) REFERENCES media_object (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176B03A8386');
        $this->addSql('DROP INDEX IDX_34DCD176B03A8386 ON person');
        $this->addSql('ALTER TABLE person CHANGE created_by_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34DCD176A76ED395 ON person (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A7E3C61F9');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A93CB796C');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A591CC992');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AB03A8386');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A896DBBDE');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AC76F1F52');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176A76ED395');
        $this->addSql('DROP INDEX UNIQ_34DCD176A76ED395 ON person');
        $this->addSql('ALTER TABLE person CHANGE user_id created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_34DCD176B03A8386 ON person (created_by_id)');
    }
}
