<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240905113124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VSGP_GamePlatformApplications (id INT AUTO_INCREMENT NOT NULL, application_id INT DEFAULT NULL, settings_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3F6F4A3B3E030ACD (application_id), INDEX IDX_3F6F4A3B59949888 (settings_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE VSGP_GamePlatformSettings (id INT AUTO_INCREMENT NOT NULL, settings_key VARCHAR(32) NOT NULL, timeout_between_players INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE VSGP_GamePlatformApplications ADD CONSTRAINT FK_3F6F4A3B3E030ACD FOREIGN KEY (application_id) REFERENCES VSAPP_Applications (id)');
        $this->addSql('ALTER TABLE VSGP_GamePlatformApplications ADD CONSTRAINT FK_3F6F4A3B59949888 FOREIGN KEY (settings_id) REFERENCES VSGP_GamePlatformSettings (id)');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id maintenance_page_id  INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id ) REFERENCES VSCMS_Pages (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id )');
        $this->addSql('ALTER TABLE VSGP_GamePlayers CHANGE type type ENUM(\'computer\', \'user\')');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title ENUM(\'mr\', \'mrs\', \'miss\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_GamePlatformApplications DROP FOREIGN KEY FK_3F6F4A3B3E030ACD');
        $this->addSql('ALTER TABLE VSGP_GamePlatformApplications DROP FOREIGN KEY FK_3F6F4A3B59949888');
        $this->addSql('DROP TABLE VSGP_GamePlatformApplications');
        $this->addSql('DROP TABLE VSGP_GamePlatformSettings');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id  maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
        $this->addSql('ALTER TABLE VSGP_GamePlayers CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title VARCHAR(255) DEFAULT NULL');
    }
}
