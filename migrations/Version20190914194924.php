<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190914194924 extends AbstractMigration
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

        $this->addSql('ALTER TABLE agent_stat ADD seer INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD trekker INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD builder INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD connector INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE agent_stat ADD mind_controller INT DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE agent_stat ADD illuminator INT DEFAULT NULL'
        );
        $this->addSql('ALTER TABLE agent_stat ADD recharger INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD liberator INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD pioneer INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD engineer INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD purifier INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD specops INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD hacker INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD translator INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD sojourner INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD recruiter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE agent_stat ADD missionday INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE agent_stat ADD nl1331meetups INT DEFAULT NULL'
        );
        $this->addSql('ALTER TABLE agent_stat ADD ifs INT DEFAULT NULL');
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
        $this->addSql('ALTER TABLE agent_stat DROP seer');
        $this->addSql('ALTER TABLE agent_stat DROP trekker');
        $this->addSql('ALTER TABLE agent_stat DROP builder');
        $this->addSql('ALTER TABLE agent_stat DROP connector');
        $this->addSql('ALTER TABLE agent_stat DROP mind_controller');
        $this->addSql('ALTER TABLE agent_stat DROP illuminator');
        $this->addSql('ALTER TABLE agent_stat DROP recharger');
        $this->addSql('ALTER TABLE agent_stat DROP liberator');
        $this->addSql('ALTER TABLE agent_stat DROP pioneer');
        $this->addSql('ALTER TABLE agent_stat DROP engineer');
        $this->addSql('ALTER TABLE agent_stat DROP purifier');
        $this->addSql('ALTER TABLE agent_stat DROP specops');
        $this->addSql('ALTER TABLE agent_stat DROP hacker');
        $this->addSql('ALTER TABLE agent_stat DROP translator');
        $this->addSql('ALTER TABLE agent_stat DROP sojourner');
        $this->addSql('ALTER TABLE agent_stat DROP recruiter');
        $this->addSql('ALTER TABLE agent_stat DROP missionday');
        $this->addSql('ALTER TABLE agent_stat DROP nl1331meetups');
        $this->addSql('ALTER TABLE agent_stat DROP ifs');
    }
}
