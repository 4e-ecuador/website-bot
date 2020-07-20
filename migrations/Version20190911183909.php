<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190911183909 extends AbstractMigration
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

        $this->addSql(
            'CREATE SEQUENCE agent_stat_id_seq INCREMENT BY 1 MINVALUE 1 START 1'
        );
        $this->addSql(
            'CREATE TABLE agent_stat (id INT NOT NULL, agent_id_id INT NOT NULL, datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ap INT NOT NULL, explorer INT NOT NULL, recon INT NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql(
            'CREATE INDEX IDX_8D2345C246EAB62F ON agent_stat (agent_id_id)'
        );
        $this->addSql(
            'ALTER TABLE agent_stat ADD CONSTRAINT FK_8D2345C246EAB62F FOREIGN KEY (agent_id_id) REFERENCES agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
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
        $this->addSql('DROP SEQUENCE agent_stat_id_seq CASCADE');
        $this->addSql('DROP TABLE agent_stat');
    }
}
