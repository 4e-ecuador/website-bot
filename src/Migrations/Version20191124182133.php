<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191124182133 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE event DROP CONSTRAINT fk_3bae0aa7c54c8c93');
        $this->addSql('DROP SEQUENCE event_type_id_seq CASCADE');
        $this->addSql('DROP TABLE event_type');
        $this->addSql('DROP INDEX idx_3bae0aa7c54c8c93');
        $this->addSql('ALTER TABLE event DROP type_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE event_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE event_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE event ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT fk_3bae0aa7c54c8c93 FOREIGN KEY (type_id) REFERENCES event_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_3bae0aa7c54c8c93 ON event (type_id)');
    }
}
