<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607051857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE VSAPI_RefreshTokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_BB25E413C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_GamePictures (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, original_name VARCHAR(255) DEFAULT '' NOT NULL COMMENT 'The Original Name of the File.', UNIQUE INDEX UNIQ_693255477E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_GamePlatformApplications (id INT AUTO_INCREMENT NOT NULL, application_id INT DEFAULT NULL, settings_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3F6F4A3B3E030ACD (application_id), INDEX IDX_3F6F4A3B59949888 (settings_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_GamePlatformSettings (id INT AUTO_INCREMENT NOT NULL, settings_key VARCHAR(32) NOT NULL, timeout_between_players INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_GamePlayers (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, guid VARCHAR(40) DEFAULT NULL, type ENUM('computer', 'user'), elo INT DEFAULT NULL, game_count INT DEFAULT NULL, gold INT DEFAULT NULL, last_free_gold DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', photo_url VARCHAR(255) DEFAULT NULL, show_photo TINYINT(1) DEFAULT 0 NOT NULL, mute_intro TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_B68C5E9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_GameSessions (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, guid VARCHAR(40) DEFAULT NULL, winner VARCHAR(40) DEFAULT NULL, score JSON DEFAULT NULL, active TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7C2EE794E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_Games (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, parent_game_id INT DEFAULT NULL, enabled TINYINT(1) DEFAULT 0 NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, position INT NOT NULL, game_url VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_19CA8883989D9B62 (slug), INDEX IDX_19CA888312469DE2 (category_id), INDEX IDX_19CA888396C7AD43 (parent_game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_GamesCategories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, taxon_id INT DEFAULT NULL, INDEX IDX_BC31D70F727ACA70 (parent_id), UNIQUE INDEX UNIQ_BC31D70FDE13F470 (taxon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_MercureConnections (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, active TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_76EFCCAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE VSGP_TempPlayers (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, game_id INT DEFAULT NULL, guid VARCHAR(40) DEFAULT NULL, name VARCHAR(255) NOT NULL, color ENUM('black', 'white'), INDEX IDX_1CCF81699E6F5DF (player_id), INDEX IDX_1CCF816E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamePictures ADD CONSTRAINT FK_693255477E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSGP_Games (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamePlatformApplications ADD CONSTRAINT FK_3F6F4A3B3E030ACD FOREIGN KEY (application_id) REFERENCES VSAPP_Applications (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamePlatformApplications ADD CONSTRAINT FK_3F6F4A3B59949888 FOREIGN KEY (settings_id) REFERENCES VSGP_GamePlatformSettings (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamePlayers ADD CONSTRAINT FK_B68C5E9A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GameSessions ADD CONSTRAINT FK_7C2EE794E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_Games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_Games ADD CONSTRAINT FK_19CA888312469DE2 FOREIGN KEY (category_id) REFERENCES VSGP_GamesCategories (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_Games ADD CONSTRAINT FK_19CA888396C7AD43 FOREIGN KEY (parent_game_id) REFERENCES VSGP_Games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamesCategories ADD CONSTRAINT FK_BC31D70F727ACA70 FOREIGN KEY (parent_id) REFERENCES VSGP_GamesCategories (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamesCategories ADD CONSTRAINT FK_BC31D70FDE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_MercureConnections ADD CONSTRAINT FK_76EFCCAA76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_TempPlayers ADD CONSTRAINT FK_1CCF81699E6F5DF FOREIGN KEY (player_id) REFERENCES VSGP_GamePlayers (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_TempPlayers ADD CONSTRAINT FK_1CCF816E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_GameSessions (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id maintenance_page_id  INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id ) REFERENCES VSCMS_Pages (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id )
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSUM_Users ADD last_active_at DATETIME DEFAULT NULL, ADD google_authenticator_secret VARCHAR(255) DEFAULT NULL, ADD api_verify_siganature VARCHAR(255) DEFAULT NULL, ADD api_verify_expires_at DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSUM_UsersInfo CHANGE title title ENUM('mr', 'mrs', 'miss')
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamePictures DROP FOREIGN KEY FK_693255477E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamePlatformApplications DROP FOREIGN KEY FK_3F6F4A3B3E030ACD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamePlatformApplications DROP FOREIGN KEY FK_3F6F4A3B59949888
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamePlayers DROP FOREIGN KEY FK_B68C5E9A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GameSessions DROP FOREIGN KEY FK_7C2EE794E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_Games DROP FOREIGN KEY FK_19CA888312469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_Games DROP FOREIGN KEY FK_19CA888396C7AD43
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamesCategories DROP FOREIGN KEY FK_BC31D70F727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_GamesCategories DROP FOREIGN KEY FK_BC31D70FDE13F470
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_MercureConnections DROP FOREIGN KEY FK_76EFCCAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_TempPlayers DROP FOREIGN KEY FK_1CCF81699E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSGP_TempPlayers DROP FOREIGN KEY FK_1CCF816E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSAPI_RefreshTokens
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_GamePictures
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_GamePlatformApplications
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_GamePlatformSettings
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_GamePlayers
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_GameSessions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_Games
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_GamesCategories
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_MercureConnections
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE VSGP_TempPlayers
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id  maintenance_page_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSUM_Users DROP last_active_at, DROP google_authenticator_secret, DROP api_verify_siganature, DROP api_verify_expires_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE VSUM_UsersInfo CHANGE title title VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
