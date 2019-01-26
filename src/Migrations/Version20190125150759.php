<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190125150759 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE invitation (code VARCHAR(6) NOT NULL, email VARCHAR(256) NOT NULL, sent BOOLEAN NOT NULL, PRIMARY KEY(code))');
        $this->addSql('ALTER TABLE fos_user ADD invitation_id VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A6479A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES invitation (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479A35D7AF0 ON fos_user (invitation_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT FK_957A6479A35D7AF0');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP INDEX UNIQ_957A6479A35D7AF0');
        $this->addSql('ALTER TABLE fos_user DROP invitation_id');
    }
}
