<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012183607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VSGP_Games (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, position INT DEFAULT 999000 NOT NULL, UNIQUE INDEX UNIQ_19CA8883989D9B62 (slug), INDEX IDX_19CA888312469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE VSGP_GamesCategories (id INT AUTO_INCREMENT NOT NULL, taxon_id INT NOT NULL, parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_BC31D70FDE13F470 (taxon_id), INDEX IDX_BC31D70F727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE VSGP_Games ADD CONSTRAINT FK_19CA888312469DE2 FOREIGN KEY (category_id) REFERENCES VSGP_GamesCategories (id)');
        $this->addSql('ALTER TABLE VSGP_GamesCategories ADD CONSTRAINT FK_BC31D70FDE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSGP_GamesCategories ADD CONSTRAINT FK_BC31D70F727ACA70 FOREIGN KEY (parent_id) REFERENCES VSGP_GamesCategories (id)');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id maintenance_page_id  INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id ) REFERENCES VSCMS_Pages (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id )');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_Games DROP FOREIGN KEY FK_19CA888312469DE2');
        $this->addSql('ALTER TABLE VSGP_GamesCategories DROP FOREIGN KEY FK_BC31D70FDE13F470');
        $this->addSql('ALTER TABLE VSGP_GamesCategories DROP FOREIGN KEY FK_BC31D70F727ACA70');
        $this->addSql('DROP TABLE VSGP_Games');
        $this->addSql('DROP TABLE VSGP_GamesCategories');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id  maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
    }
}
