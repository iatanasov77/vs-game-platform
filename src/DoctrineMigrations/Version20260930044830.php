<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260930044830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD CONSTRAINT FK_7C2EE7947E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSGP_GamePlayers (id)');
        $this->addSql('CREATE INDEX IDX_7C2EE7947E3C61F9 ON VSGP_GameSessions (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_GameSessions DROP FOREIGN KEY FK_7C2EE7947E3C61F9');
        $this->addSql('DROP INDEX IDX_7C2EE7947E3C61F9 ON VSGP_GameSessions');
        $this->addSql('ALTER TABLE VSGP_GameSessions DROP owner_id');
    }
}
