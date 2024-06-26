<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200606172950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE agent_stat ADD drone_flight_distance INT DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE agent_stat ADD drone_hacks INT DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE agent_stat ADD drone_portals_visited INT DEFAULT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE agent_stat DROP drone_flight_distance');
        $this->addSql('ALTER TABLE agent_stat DROP drone_hacks');
        $this->addSql('ALTER TABLE agent_stat DROP drone_portals_visited');
    }
}
