<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190613171401 extends AbstractMigration
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

        $this->addSql('DROP SEQUENCE new_table_name_id_seq CASCADE');
        $this->addSql(
            'CREATE SEQUENCE agent_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1'
        );
        $this->addSql(
            'CREATE TABLE agent_user (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_20086CAAF85E0677 ON agent_user (username)'
        );
        $this->addSql('DROP TABLE new_table_name');
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
        $this->addSql('DROP SEQUENCE agent_user_id_seq CASCADE');
        $this->addSql(
            'CREATE SEQUENCE new_table_name_id_seq INCREMENT BY 1 MINVALUE 1 START 1'
        );
        $this->addSql(
            'CREATE TABLE new_table_name (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql(
            'CREATE UNIQUE INDEX uniq_c9cadd01f85e0677 ON new_table_name (username)'
        );
        $this->addSql('DROP TABLE agent_user');
    }
}
