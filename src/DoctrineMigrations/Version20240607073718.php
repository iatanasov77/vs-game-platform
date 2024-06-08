<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240607073718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VSAPI_RefreshTokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_BB25E413C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE VSGP_GamePictures (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, type VARCHAR(255) DEFAULT NULL, path VARCHAR(255) NOT NULL, original_name VARCHAR(255) DEFAULT \'\' NOT NULL COMMENT \'The Original Name of the File.\', UNIQUE INDEX UNIQ_693255477E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE VSGP_Games (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, picture_id INT DEFAULT NULL, enabled TINYINT(1) DEFAULT 0 NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, position INT NOT NULL, game_url VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_19CA8883989D9B62 (slug), INDEX IDX_19CA888312469DE2 (category_id), UNIQUE INDEX UNIQ_19CA8883EE45BDBF (picture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE VSGP_GamesCategories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, taxon_id INT DEFAULT NULL, INDEX IDX_BC31D70F727ACA70 (parent_id), UNIQUE INDEX UNIQ_BC31D70FDE13F470 (taxon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE VSGP_GamePictures ADD CONSTRAINT FK_693255477E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSGP_Games (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSGP_Games ADD CONSTRAINT FK_19CA888312469DE2 FOREIGN KEY (category_id) REFERENCES VSGP_GamesCategories (id)');
        $this->addSql('ALTER TABLE VSGP_Games ADD CONSTRAINT FK_19CA8883EE45BDBF FOREIGN KEY (picture_id) REFERENCES VSGP_GamePictures (id)');
        $this->addSql('ALTER TABLE VSGP_GamesCategories ADD CONSTRAINT FK_BC31D70F727ACA70 FOREIGN KEY (parent_id) REFERENCES VSGP_GamesCategories (id)');
        $this->addSql('ALTER TABLE VSGP_GamesCategories ADD CONSTRAINT FK_BC31D70FDE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id maintenance_page_id  INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id ) REFERENCES VSCMS_Pages (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id )');
        $this->addSql('ALTER TABLE VSCAT_Products DROP average_rating');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title ENUM(\'mr\', \'mrs\', \'miss\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_GamePictures DROP FOREIGN KEY FK_693255477E3C61F9');
        $this->addSql('ALTER TABLE VSGP_Games DROP FOREIGN KEY FK_19CA888312469DE2');
        $this->addSql('ALTER TABLE VSGP_Games DROP FOREIGN KEY FK_19CA8883EE45BDBF');
        $this->addSql('ALTER TABLE VSGP_GamesCategories DROP FOREIGN KEY FK_BC31D70F727ACA70');
        $this->addSql('ALTER TABLE VSGP_GamesCategories DROP FOREIGN KEY FK_BC31D70FDE13F470');
        $this->addSql('DROP TABLE VSAPI_RefreshTokens');
        $this->addSql('DROP TABLE VSGP_GamePictures');
        $this->addSql('DROP TABLE VSGP_Games');
        $this->addSql('DROP TABLE VSGP_GamesCategories');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id  maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
        $this->addSql('ALTER TABLE VSCAT_Products ADD average_rating DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title VARCHAR(255) DEFAULT NULL');
    }
}
