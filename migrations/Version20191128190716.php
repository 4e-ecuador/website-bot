<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191128190716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE SEQUENCE ingress_event_id_seq INCREMENT BY 1 MINVALUE 1 START 1'
        );
        $this->addSql(
            'CREATE TABLE ingress_event (id INT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, date_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE ingress_event_id_seq CASCADE');
        $this->addSql('DROP TABLE ingress_event');
    }
}
