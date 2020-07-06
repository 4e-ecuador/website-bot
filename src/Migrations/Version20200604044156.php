<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200604044156 extends AbstractMigration
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
            !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('DROP SEQUENCE config_id_seq CASCADE');
        $this->addSql('DROP TABLE config');
        $this->addSql('ALTER TABLE agent ADD telegram_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE agent ADD telegram_connection_secret VARCHAR(48) DEFAULT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName()
            !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SCHEMA public');
        $this->addSql(
            'CREATE SEQUENCE config_id_seq INCREMENT BY 1 MINVALUE 1 START 1'
        );
        $this->addSql(
            'CREATE TABLE config (id INT NOT NULL, encryption_key BYTEA NOT NULL, encryption_key_encoded TEXT NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('ALTER TABLE agent DROP telegram_id');
        $this->addSql('ALTER TABLE agent DROP telegram_connection_secret');
    }
}
