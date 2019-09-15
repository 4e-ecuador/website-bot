<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190915170306 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE agent_stat DROP CONSTRAINT fk_8d2345c246eab62f');
        $this->addSql('DROP INDEX idx_8d2345c246eab62f');
        $this->addSql('ALTER TABLE agent_stat RENAME COLUMN agent_id_id TO agent_id');
        $this->addSql('ALTER TABLE agent_stat ADD CONSTRAINT FK_8D2345C23414710B FOREIGN KEY (agent_id) REFERENCES agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D2345C23414710B ON agent_stat (agent_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE agent_stat DROP CONSTRAINT FK_8D2345C23414710B');
        $this->addSql('DROP INDEX IDX_8D2345C23414710B');
        $this->addSql('ALTER TABLE agent_stat RENAME COLUMN agent_id TO agent_id_id');
        $this->addSql('ALTER TABLE agent_stat ADD CONSTRAINT fk_8d2345c246eab62f FOREIGN KEY (agent_id_id) REFERENCES agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_8d2345c246eab62f ON agent_stat (agent_id_id)');
    }
}
