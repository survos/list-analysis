<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190123145647 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE time_period_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE archive_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE time_period (id INT NOT NULL, month_number INT NOT NULL, year INT NOT NULL, calculated_message_count INT NOT NULL, imported_message_count INT NOT NULL, loc_message_count INT NOT NULL, subject_count INT NOT NULL, md5 VARCHAR(32) DEFAULT NULL, filesize INT DEFAULT NULL, marking VARCHAR(24) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE message (id VARCHAR(255) NOT NULL, account_id INT DEFAULT NULL, time_period_id INT DEFAULT NULL, in_reply_to_id VARCHAR(255) DEFAULT NULL, from_text TEXT NOT NULL, time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, body TEXT NOT NULL, subject VARCHAR(160) NOT NULL, raw_message_id VARCHAR(255) DEFAULT NULL, is_local BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6BD307F9B6B5FBA ON message (account_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307F7EFD7106 ON message (time_period_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FDD92DAB8 ON message (in_reply_to_id)');
        $this->addSql('CREATE TABLE account (id INT NOT NULL, sender VARCHAR(100) NOT NULL, sender_name VARCHAR(80) NOT NULL, count INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A45F004ACF ON account (sender)');
        $this->addSql('CREATE INDEX count_idx ON account (count)');
        $this->addSql('CREATE TABLE archive (id INT NOT NULL, filename VARCHAR(32) NOT NULL, message_count INT DEFAULT NULL, marking VARCHAR(32) NOT NULL, line_count INT DEFAULT NULL, zipped_file_size INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7EFD7106 FOREIGN KEY (time_period_id) REFERENCES time_period (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FDD92DAB8 FOREIGN KEY (in_reply_to_id) REFERENCES message (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F7EFD7106');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307FDD92DAB8');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F9B6B5FBA');
        $this->addSql('DROP SEQUENCE time_period_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE archive_id_seq CASCADE');
        $this->addSql('DROP TABLE time_period');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE archive');
    }
}
