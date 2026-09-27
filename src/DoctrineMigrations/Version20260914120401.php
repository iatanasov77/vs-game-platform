<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914120401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD status ENUM(\'waiting\', \'playing\', \'full\') DEFAULT \'waiting\', DROP active');
        $this->addSql('ALTER TABLE VSGP_Games ADD max_players INT DEFAULT 4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_Games DROP max_players');
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD active TINYINT DEFAULT 0 NOT NULL, DROP status');
    }
}
