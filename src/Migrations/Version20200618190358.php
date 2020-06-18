<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200618190358 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_20086caaf85e0677');
        $this->addSql('ALTER TABLE agent_user DROP username');
        $this->addSql('ALTER TABLE agent_user DROP password');
        $this->addSql('ALTER TABLE agent_user ALTER email SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_20086CAAE7927C74 ON agent_user (email)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_20086CAAE7927C74');
        $this->addSql('ALTER TABLE agent_user ADD username VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE agent_user ADD password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_user ALTER email DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_20086caaf85e0677 ON agent_user (username)');
    }
}
