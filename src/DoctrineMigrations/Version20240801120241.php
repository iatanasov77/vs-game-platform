<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240801120241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VSGP_GameRooms (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, INDEX IDX_A1C04365E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE VSGP_GameRooms_Players (game_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_2CFCF2EEE48FD905 (game_id), INDEX IDX_2CFCF2EE99E6F5DF (player_id), PRIMARY KEY(game_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE VSGP_GameRooms ADD CONSTRAINT FK_A1C04365E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_Games (id)');
        $this->addSql('ALTER TABLE VSGP_GameRooms_Players ADD CONSTRAINT FK_2CFCF2EEE48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_GameRooms (id)');
        $this->addSql('ALTER TABLE VSGP_GameRooms_Players ADD CONSTRAINT FK_2CFCF2EE99E6F5DF FOREIGN KEY (player_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id maintenance_page_id  INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id ) REFERENCES VSCMS_Pages (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id )');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title ENUM(\'mr\', \'mrs\', \'miss\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_GameRooms DROP FOREIGN KEY FK_A1C04365E48FD905');
        $this->addSql('ALTER TABLE VSGP_GameRooms_Players DROP FOREIGN KEY FK_2CFCF2EEE48FD905');
        $this->addSql('ALTER TABLE VSGP_GameRooms_Players DROP FOREIGN KEY FK_2CFCF2EE99E6F5DF');
        $this->addSql('DROP TABLE VSGP_GameRooms');
        $this->addSql('DROP TABLE VSGP_GameRooms_Players');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id  maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title VARCHAR(255) DEFAULT NULL');
    }
}
