<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260908042712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories ADD CONSTRAINT FK_10C2B955727ACA70 FOREIGN KEY (parent_id) REFERENCES VSCAT_PricingPlanCategories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories ADD CONSTRAINT FK_10C2B955DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions ADD CONSTRAINT FK_EA3E01A0A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSCAT_ProductCategories ADD CONSTRAINT FK_7ADE9A79DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSGP_Games CHANGE position position INT DEFAULT 0');
        $this->addSql('ALTER TABLE VSPAY_CustomerGroups ADD CONSTRAINT FK_8D3A9BC4DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id)');
        $this->addSql('ALTER TABLE VSPAY_Order ADD CONSTRAINT FK_87954502A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Applications ADD CONSTRAINT FK_1D3F36D53E030ACD FOREIGN KEY (application_id) REFERENCES VSAPP_Applications (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title ENUM(\'mr\', \'mrs\', \'miss\') DEFAULT \'miss\'');
        $this->addSql('ALTER TABLE VSUS_NewsletterSubscriptions ADD CONSTRAINT FK_E521F0DCA76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories DROP FOREIGN KEY FK_10C2B955727ACA70');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories DROP FOREIGN KEY FK_10C2B955DE13F470');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions DROP FOREIGN KEY FK_EA3E01A0A76ED395');
        $this->addSql('ALTER TABLE VSCAT_ProductCategories DROP FOREIGN KEY FK_7ADE9A79DE13F470');
        $this->addSql('ALTER TABLE VSGP_Games CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE VSPAY_CustomerGroups DROP FOREIGN KEY FK_8D3A9BC4DE13F470');
        $this->addSql('ALTER TABLE VSPAY_Order DROP FOREIGN KEY FK_87954502A76ED395');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Applications DROP FOREIGN KEY FK_1D3F36D53E030ACD');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title ENUM(\'mr\', \'mrs\', \'miss\') DEFAULT NULL');
        $this->addSql('ALTER TABLE VSUS_NewsletterSubscriptions DROP FOREIGN KEY FK_E521F0DCA76ED395');
    }
}
