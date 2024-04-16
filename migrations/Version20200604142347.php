<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200604142347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE agent ADD has_notify_upload_stats BOOLEAN DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE agent ADD has_notify_events BOOLEAN DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE agent ADD has_notify_stats_result BOOLEAN DEFAULT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE agent DROP has_notify_upload_stats');
        $this->addSql('ALTER TABLE agent DROP has_notify_events');
        $this->addSql('ALTER TABLE agent DROP has_notify_stats_result');
    }
}
