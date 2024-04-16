<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190914173013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agent_user ADD agent_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE agent_user ADD CONSTRAINT FK_20086CAA3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_20086CAA3414710B ON agent_user (agent_id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql(
            'ALTER TABLE agent_user DROP CONSTRAINT FK_20086CAA3414710B'
        );
        $this->addSql('DROP INDEX UNIQ_20086CAA3414710B');
        $this->addSql('ALTER TABLE agent_user DROP agent_id');
    }
}
