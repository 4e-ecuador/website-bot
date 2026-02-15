<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215130102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create login_attempt table for tracking authentication events';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE login_attempt_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE login_attempt (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, email VARCHAR(180) DEFAULT NULL, success BOOLEAN NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, auth_method VARCHAR(30) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE login_attempt_id_seq CASCADE');
        $this->addSql('DROP TABLE login_attempt');
    }
}
