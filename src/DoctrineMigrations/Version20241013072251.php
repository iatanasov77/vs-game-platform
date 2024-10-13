<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241013072251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id maintenance_page_id  INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id ) REFERENCES VSCMS_Pages (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id )');
        $this->addSql('ALTER TABLE VSGP_GamePlayers ADD photo_url VARCHAR(255) DEFAULT NULL, ADD show_photo TINYINT(1) DEFAULT 0 NOT NULL, CHANGE type type ENUM(\'computer\', \'user\')');
        $this->addSql('ALTER TABLE VSGP_TempPlayers ADD CONSTRAINT FK_1CCF81699E6F5DF FOREIGN KEY (player_id) REFERENCES VSGP_GamePlayers (id)');
        $this->addSql('ALTER TABLE VSGP_TempPlayers ADD CONSTRAINT FK_1CCF816E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_GameSessions (id)');
        $this->addSql('ALTER TABLE VSUM_AvatarImage ADD CONSTRAINT FK_D917FB667E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSUM_UsersInfo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSUM_ResetPasswordRequests ADD CONSTRAINT FK_D6C66D0A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSUM_UserRoles ADD CONSTRAINT FK_7F8AAD7E727ACA70 FOREIGN KEY (parent_id) REFERENCES VSUM_UserRoles (id)');
        $this->addSql('ALTER TABLE VSUM_UserRoles ADD CONSTRAINT FK_7F8AAD7EDE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSUM_Users ADD CONSTRAINT FK_CAFDCD035D8BC1F8 FOREIGN KEY (info_id) REFERENCES VSUM_UsersInfo (id)');
        $this->addSql('ALTER TABLE VSUM_Users_Roles ADD CONSTRAINT FK_82E26E77A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSUM_Users_Roles ADD CONSTRAINT FK_82E26E77D60322AC FOREIGN KEY (role_id) REFERENCES VSUM_UserRoles (id)');
        $this->addSql('ALTER TABLE VSUM_UsersActivities ADD CONSTRAINT FK_54103277A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title ENUM(\'mr\', \'mrs\', \'miss\')');
        $this->addSql('ALTER TABLE VSUM_UsersNotifications ADD CONSTRAINT FK_8D75FA15A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id  maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
        $this->addSql('ALTER TABLE VSGP_GamePlayers DROP photo_url, DROP show_photo, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE VSGP_TempPlayers DROP FOREIGN KEY FK_1CCF81699E6F5DF');
        $this->addSql('ALTER TABLE VSGP_TempPlayers DROP FOREIGN KEY FK_1CCF816E48FD905');
        $this->addSql('ALTER TABLE VSUM_AvatarImage DROP FOREIGN KEY FK_D917FB667E3C61F9');
        $this->addSql('ALTER TABLE VSUM_ResetPasswordRequests DROP FOREIGN KEY FK_D6C66D0A76ED395');
        $this->addSql('ALTER TABLE VSUM_UserRoles DROP FOREIGN KEY FK_7F8AAD7E727ACA70');
        $this->addSql('ALTER TABLE VSUM_UserRoles DROP FOREIGN KEY FK_7F8AAD7EDE13F470');
        $this->addSql('ALTER TABLE VSUM_Users DROP FOREIGN KEY FK_CAFDCD035D8BC1F8');
        $this->addSql('ALTER TABLE VSUM_UsersActivities DROP FOREIGN KEY FK_54103277A76ED395');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE VSUM_UsersNotifications DROP FOREIGN KEY FK_8D75FA15A76ED395');
        $this->addSql('ALTER TABLE VSUM_Users_Roles DROP FOREIGN KEY FK_82E26E77A76ED395');
        $this->addSql('ALTER TABLE VSUM_Users_Roles DROP FOREIGN KEY FK_82E26E77D60322AC');
    }
}
