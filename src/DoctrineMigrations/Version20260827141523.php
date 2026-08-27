<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260827141523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY `FK_4A491FD507FAB6A`');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP maintenanceMode, DROP maintenance_page_id');
        $this->addSql('ALTER TABLE VSCMS_QuickLinks_Categories ADD CONSTRAINT FK_1EC78068A74E21B5 FOREIGN KEY (quick_link_id) REFERENCES VSCMS_QuickLinks (id)');
        $this->addSql('ALTER TABLE VSCMS_QuickLinks_Categories ADD CONSTRAINT FK_1EC7806812469DE2 FOREIGN KEY (category_id) REFERENCES VSCMS_QuickLinksCategories (id)');
        $this->addSql('ALTER TABLE VSCMS_QuickLinksCategories ADD CONSTRAINT FK_3AA6C0F5DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSGP_GamePlatformSettings ADD debug_dummy_player_cards TINYINT DEFAULT 0');
        $this->addSql('ALTER TABLE VSGP_GamePlayers CHANGE type type ENUM(\'computer\', \'user\') DEFAULT \'user\', CHANGE last_free_gold last_free_gold DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE VSGP_Games CHANGE status status ENUM(\'not_implemented\', \'in_developement\', \'in_developement_but\', \'game_is_done\') DEFAULT \'not_implemented\'');
        $this->addSql('ALTER TABLE VSGP_TempPlayers CHANGE color color ENUM(\'black\', \'white\') DEFAULT \'black\', CHANGE position position ENUM(\'north\', \'south\', \'east\', \'west\') DEFAULT \'south\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSAPP_Settings ADD maintenanceMode TINYINT DEFAULT 0 NOT NULL COMMENT \'This Application is In Maintenace Mode.\', ADD maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT `FK_4A491FD507FAB6A` FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
        $this->addSql('ALTER TABLE VSCMS_QuickLinksCategories DROP FOREIGN KEY FK_3AA6C0F5DE13F470');
        $this->addSql('ALTER TABLE VSCMS_QuickLinks_Categories DROP FOREIGN KEY FK_1EC78068A74E21B5');
        $this->addSql('ALTER TABLE VSCMS_QuickLinks_Categories DROP FOREIGN KEY FK_1EC7806812469DE2');
        $this->addSql('ALTER TABLE VSGP_GamePlatformSettings DROP debug_dummy_player_cards');
        $this->addSql('ALTER TABLE VSGP_GamePlayers CHANGE type type ENUM(\'computer\', \'user\') DEFAULT NULL, CHANGE last_free_gold last_free_gold DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE VSGP_Games CHANGE status status ENUM(\'not_implemented\', \'in_developement\', \'in_developement_but\', \'game_is_done\') DEFAULT NULL');
        $this->addSql('ALTER TABLE VSGP_TempPlayers CHANGE color color ENUM(\'black\', \'white\') DEFAULT NULL, CHANGE position position ENUM(\'north\', \'south\', \'east\', \'west\') DEFAULT NULL');
    }
}
