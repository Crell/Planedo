<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211108174519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE feed_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE feed_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE feed (id INT NOT NULL, title VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE feed_entry (id INT NOT NULL, feed_id INT NOT NULL, title VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, summary TEXT NOT NULL, author_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DEAECECC51A5BC03 ON feed_entry (feed_id)');
        $this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECC51A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE feed_entry DROP CONSTRAINT FK_DEAECECC51A5BC03');
        $this->addSql('DROP SEQUENCE feed_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE feed_entry_id_seq CASCADE');
        $this->addSql('DROP TABLE feed');
        $this->addSql('DROP TABLE feed_entry');
    }
}
