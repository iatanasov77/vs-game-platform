<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261002043641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VSAPI_RefreshTokens (refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, UNIQUE INDEX UNIQ_BB25E413C74F2195 (refresh_token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_GamePictures (type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, original_name VARCHAR(255) DEFAULT \'\' NOT NULL COMMENT \'The Original Name of the File.\', id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, UNIQUE INDEX UNIQ_693255477E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_GamePlatformApplications (id INT AUTO_INCREMENT NOT NULL, application_id INT DEFAULT NULL, settings_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3F6F4A3B3E030ACD (application_id), INDEX IDX_3F6F4A3B59949888 (settings_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_GamePlatformSettings (id INT AUTO_INCREMENT NOT NULL, settings_key VARCHAR(32) NOT NULL, timeout_between_players INT NOT NULL, game_player_inactivity_timeout INT NOT NULL, remove_leaved_game_sessions_for_player TINYINT DEFAULT 0, auto_open_card_game_auction_dialog TINYINT DEFAULT 0, debug_game_sounds TINYINT DEFAULT 0, debug_card_game_player_areas TINYINT DEFAULT 0, debug_card_game_player_cards TINYINT DEFAULT 0, debug_dummy_player_cards TINYINT DEFAULT 0, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_GamePlayers (id INT AUTO_INCREMENT NOT NULL, guid VARCHAR(40) DEFAULT NULL, type ENUM(\'computer\', \'user\') DEFAULT \'user\', elo INT DEFAULT 0 NOT NULL, game_count INT DEFAULT 0 NOT NULL, gold INT DEFAULT 200 NOT NULL, last_free_gold DATETIME DEFAULT NULL, photo_url VARCHAR(255) DEFAULT NULL, show_photo TINYINT DEFAULT 0 NOT NULL, mute_intro TINYINT DEFAULT 0 NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B68C5E9A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_GameSessions (id INT AUTO_INCREMENT NOT NULL, guid VARCHAR(40) DEFAULT NULL, winner VARCHAR(40) DEFAULT NULL, score JSON DEFAULT NULL, status ENUM(\'waiting\', \'playing\', \'full\') DEFAULT \'waiting\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, owner_id INT DEFAULT NULL, game_id INT DEFAULT NULL, INDEX IDX_7C2EE7947E3C61F9 (owner_id), INDEX IDX_7C2EE794E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_Games (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT DEFAULT 0 NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, position INT DEFAULT 0, game_url VARCHAR(255) DEFAULT NULL, max_players INT DEFAULT 4, max_team_players INT DEFAULT 2, type ENUM(\'board_game\', \'card_game\', \'card_game_no_teams\') DEFAULT NULL, status ENUM(\'not_implemented\', \'in_developement\', \'in_developement_but\', \'game_is_done\') DEFAULT \'not_implemented\', category_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_19CA8883989D9B62 (slug), INDEX IDX_19CA888312469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_GamesCategories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, taxon_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_BC31D70FDE13F470 (taxon_id), INDEX IDX_BC31D70F727ACA70 (parent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_MercureConnections (id INT AUTO_INCREMENT NOT NULL, active TINYINT DEFAULT 0 NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_76EFCCAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE VSGP_TempPlayers (id INT AUTO_INCREMENT NOT NULL, guid VARCHAR(40) DEFAULT NULL, name VARCHAR(255) NOT NULL, color ENUM(\'black\', \'white\') DEFAULT \'black\', position ENUM(\'north\', \'south\', \'east\', \'west\') DEFAULT \'south\', player_id INT DEFAULT NULL, game_id INT DEFAULT NULL, INDEX IDX_1CCF81699E6F5DF (player_id), INDEX IDX_1CCF816E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE VSGP_GamePictures ADD CONSTRAINT FK_693255477E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSGP_Games (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSGP_GamePlatformApplications ADD CONSTRAINT FK_3F6F4A3B3E030ACD FOREIGN KEY (application_id) REFERENCES VSAPP_Applications (id)');
        $this->addSql('ALTER TABLE VSGP_GamePlatformApplications ADD CONSTRAINT FK_3F6F4A3B59949888 FOREIGN KEY (settings_id) REFERENCES VSGP_GamePlatformSettings (id)');
        $this->addSql('ALTER TABLE VSGP_GamePlayers ADD CONSTRAINT FK_B68C5E9A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD CONSTRAINT FK_7C2EE7947E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSGP_GamePlayers (id)');
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD CONSTRAINT FK_7C2EE794E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_Games (id)');
        $this->addSql('ALTER TABLE VSGP_Games ADD CONSTRAINT FK_19CA888312469DE2 FOREIGN KEY (category_id) REFERENCES VSGP_GamesCategories (id)');
        $this->addSql('ALTER TABLE VSGP_GamesCategories ADD CONSTRAINT FK_BC31D70F727ACA70 FOREIGN KEY (parent_id) REFERENCES VSGP_GamesCategories (id)');
        $this->addSql('ALTER TABLE VSGP_GamesCategories ADD CONSTRAINT FK_BC31D70FDE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSGP_MercureConnections ADD CONSTRAINT FK_76EFCCAA76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSGP_TempPlayers ADD CONSTRAINT FK_1CCF81699E6F5DF FOREIGN KEY (player_id) REFERENCES VSGP_GamePlayers (id)');
        $this->addSql('ALTER TABLE VSGP_TempPlayers ADD CONSTRAINT FK_1CCF816E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_GameSessions (id)');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY `FK_4A491FD507FAB6A`');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP maintenanceMode, DROP maintenance_page_id');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories ADD CONSTRAINT FK_10C2B955727ACA70 FOREIGN KEY (parent_id) REFERENCES VSCAT_PricingPlanCategories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories ADD CONSTRAINT FK_10C2B955DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions ADD CONSTRAINT FK_EA3E01A0A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSCAT_ProductCategories ADD CONSTRAINT FK_7ADE9A79DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSCMS_QuickLinks_Categories ADD CONSTRAINT FK_1EC78068A74E21B5 FOREIGN KEY (quick_link_id) REFERENCES VSCMS_QuickLinks (id)');
        $this->addSql('ALTER TABLE VSCMS_QuickLinks_Categories ADD CONSTRAINT FK_1EC7806812469DE2 FOREIGN KEY (category_id) REFERENCES VSCMS_QuickLinksCategories (id)');
        $this->addSql('ALTER TABLE VSCMS_QuickLinksCategories ADD CONSTRAINT FK_3AA6C0F5DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSPAY_CustomerGroups ADD CONSTRAINT FK_8D3A9BC4DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSPAY_Order ADD CONSTRAINT FK_87954502A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSPAY_PromotionActions CHANGE configuration configuration JSON NOT NULL');
        $this->addSql('ALTER TABLE VSPAY_PromotionRules CHANGE configuration configuration JSON NOT NULL');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Applications ADD CONSTRAINT FK_1D3F36D53E030ACD FOREIGN KEY (application_id) REFERENCES VSAPP_Applications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSUM_Users ADD last_active_at DATETIME DEFAULT NULL, ADD google_authenticator_secret VARCHAR(255) DEFAULT NULL, ADD api_verify_siganature VARCHAR(255) DEFAULT NULL, ADD api_verify_expires_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE VSUS_NewsletterSubscriptions ADD CONSTRAINT FK_E521F0DCA76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_GamePictures DROP FOREIGN KEY FK_693255477E3C61F9');
        $this->addSql('ALTER TABLE VSGP_GamePlatformApplications DROP FOREIGN KEY FK_3F6F4A3B3E030ACD');
        $this->addSql('ALTER TABLE VSGP_GamePlatformApplications DROP FOREIGN KEY FK_3F6F4A3B59949888');
        $this->addSql('ALTER TABLE VSGP_GamePlayers DROP FOREIGN KEY FK_B68C5E9A76ED395');
        $this->addSql('ALTER TABLE VSGP_GameSessions DROP FOREIGN KEY FK_7C2EE7947E3C61F9');
        $this->addSql('ALTER TABLE VSGP_GameSessions DROP FOREIGN KEY FK_7C2EE794E48FD905');
        $this->addSql('ALTER TABLE VSGP_Games DROP FOREIGN KEY FK_19CA888312469DE2');
        $this->addSql('ALTER TABLE VSGP_GamesCategories DROP FOREIGN KEY FK_BC31D70F727ACA70');
        $this->addSql('ALTER TABLE VSGP_GamesCategories DROP FOREIGN KEY FK_BC31D70FDE13F470');
        $this->addSql('ALTER TABLE VSGP_MercureConnections DROP FOREIGN KEY FK_76EFCCAA76ED395');
        $this->addSql('ALTER TABLE VSGP_TempPlayers DROP FOREIGN KEY FK_1CCF81699E6F5DF');
        $this->addSql('ALTER TABLE VSGP_TempPlayers DROP FOREIGN KEY FK_1CCF816E48FD905');
        $this->addSql('DROP TABLE VSAPI_RefreshTokens');
        $this->addSql('DROP TABLE VSGP_GamePictures');
        $this->addSql('DROP TABLE VSGP_GamePlatformApplications');
        $this->addSql('DROP TABLE VSGP_GamePlatformSettings');
        $this->addSql('DROP TABLE VSGP_GamePlayers');
        $this->addSql('DROP TABLE VSGP_GameSessions');
        $this->addSql('DROP TABLE VSGP_Games');
        $this->addSql('DROP TABLE VSGP_GamesCategories');
        $this->addSql('DROP TABLE VSGP_MercureConnections');
        $this->addSql('DROP TABLE VSGP_TempPlayers');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD maintenanceMode TINYINT DEFAULT 0 NOT NULL COMMENT \'This Application is In Maintenace Mode.\', ADD maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT `FK_4A491FD507FAB6A` FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories DROP FOREIGN KEY FK_10C2B955727ACA70');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories DROP FOREIGN KEY FK_10C2B955DE13F470');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions DROP FOREIGN KEY FK_EA3E01A0A76ED395');
        $this->addSql('ALTER TABLE VSCAT_ProductCategories DROP FOREIGN KEY FK_7ADE9A79DE13F470');
        $this->addSql('ALTER TABLE VSCMS_QuickLinksCategories DROP FOREIGN KEY FK_3AA6C0F5DE13F470');
        $this->addSql('ALTER TABLE VSCMS_QuickLinks_Categories DROP FOREIGN KEY FK_1EC78068A74E21B5');
        $this->addSql('ALTER TABLE VSCMS_QuickLinks_Categories DROP FOREIGN KEY FK_1EC7806812469DE2');
        $this->addSql('ALTER TABLE VSPAY_CustomerGroups DROP FOREIGN KEY FK_8D3A9BC4DE13F470');
        $this->addSql('ALTER TABLE VSPAY_Order DROP FOREIGN KEY FK_87954502A76ED395');
        $this->addSql('ALTER TABLE VSPAY_PromotionActions CHANGE configuration configuration LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE VSPAY_PromotionRules CHANGE configuration configuration LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Applications DROP FOREIGN KEY FK_1D3F36D53E030ACD');
        $this->addSql('ALTER TABLE VSUM_Users DROP last_active_at, DROP google_authenticator_secret, DROP api_verify_siganature, DROP api_verify_expires_at');
        $this->addSql('ALTER TABLE VSUS_NewsletterSubscriptions DROP FOREIGN KEY FK_E521F0DCA76ED395');
    }
}
