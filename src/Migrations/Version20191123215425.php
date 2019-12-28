<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191123215425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName()
            !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('ALTER TABLE event_type ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event ADD type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD event_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7C54C8C93 FOREIGN KEY (type_id) REFERENCES event_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7C54C8C93 ON event (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName()
            !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7C54C8C93');
        $this->addSql('DROP INDEX IDX_3BAE0AA7C54C8C93');
        $this->addSql('ALTER TABLE event DROP type_id');
        $this->addSql('ALTER TABLE event DROP event_type');
        $this->addSql('ALTER TABLE event_type DROP name');
    }
}
