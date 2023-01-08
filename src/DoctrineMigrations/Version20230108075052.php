<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230108075052 extends AbstractMigration
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
        $this->addSql('ALTER TABLE VSCMS_TocPage CHANGE position position INT DEFAULT 999999');
        $this->addSql('ALTER TABLE VSGP_GamePictures ADD owner_id INT NOT NULL, CHANGE original_name original_name VARCHAR(255) DEFAULT \'\' NOT NULL COMMENT \'The Original Name of the File.\'');
        $this->addSql('ALTER TABLE VSGP_GamePictures ADD CONSTRAINT FK_693255477E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSGP_Games (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_693255477E3C61F9 ON VSGP_GamePictures (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id  maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
        $this->addSql('ALTER TABLE VSCMS_TocPage CHANGE position position INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSGP_GamePictures DROP FOREIGN KEY FK_693255477E3C61F9');
        $this->addSql('DROP INDEX UNIQ_693255477E3C61F9 ON VSGP_GamePictures');
        $this->addSql('ALTER TABLE VSGP_GamePictures DROP owner_id, CHANGE original_name original_name VARCHAR(255) NOT NULL COMMENT \'The Original Name of the File.\'');
    }
}
