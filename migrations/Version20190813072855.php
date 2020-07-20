<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190813072855 extends AbstractMigration
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

        $this->addSql('ALTER TABLE comment ADD commenter_id INT NOT NULL');
        $this->addSql(
            'ALTER TABLE comment ADD CONSTRAINT FK_9474526CB4D5A9E2 FOREIGN KEY (commenter_id) REFERENCES agent_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'CREATE INDEX IDX_9474526CB4D5A9E2 ON comment (commenter_id)'
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
            'ALTER TABLE comment DROP CONSTRAINT FK_9474526CB4D5A9E2'
        );
        $this->addSql('DROP INDEX IDX_9474526CB4D5A9E2');
        $this->addSql('ALTER TABLE comment DROP commenter_id');
    }
}
