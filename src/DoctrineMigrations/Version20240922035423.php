<?php

declare(strict_types=1);

namespace App\DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240922035423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VSGP_GameSessions DROP FOREIGN KEY FK_7C2EE794C1D50FBC');
        $this->addSql('ALTER TABLE VSUM_Users DROP FOREIGN KEY FK_CAFDCD03D2919A68');
        $this->addSql('CREATE TABLE VSGP_TempPlayers (id INT AUTO_INCREMENT NOT NULL, player_id INT DEFAULT NULL, game_id INT DEFAULT NULL, guid VARCHAR(40) DEFAULT NULL, name VARCHAR(255) NOT NULL, color ENUM(\'black\', \'white\'), INDEX IDX_1CCF81699E6F5DF (player_id), INDEX IDX_1CCF816E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE VSGP_TempPlayers ADD CONSTRAINT FK_1CCF81699E6F5DF FOREIGN KEY (player_id) REFERENCES VSGP_GamePlayers (id)');
        $this->addSql('ALTER TABLE VSGP_TempPlayers ADD CONSTRAINT FK_1CCF816E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_GameSessions (id)');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories DROP FOREIGN KEY FK_10C2B955DE13F470');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories DROP FOREIGN KEY FK_10C2B955727ACA70');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions DROP FOREIGN KEY FK_EA3E01A0A76ED395');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions DROP FOREIGN KEY FK_EA3E01A038248176');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions DROP FOREIGN KEY FK_EA3E01A029628C71');
        $this->addSql('ALTER TABLE VSCAT_PricingPlans DROP FOREIGN KEY FK_615E6C0587FFD8A7');
        $this->addSql('ALTER TABLE VSCAT_PricingPlans DROP FOREIGN KEY FK_615E6C0538248176');
        $this->addSql('ALTER TABLE VSCAT_PricingPlans DROP FOREIGN KEY FK_615E6C0512469DE2');
        $this->addSql('ALTER TABLE VSCAT_ProductAssociations DROP FOREIGN KEY FK_559D3973B1E1C39');
        $this->addSql('ALTER TABLE VSCAT_ProductAssociations DROP FOREIGN KEY FK_559D39734584665A');
        $this->addSql('ALTER TABLE VSCAT_ProductCategories DROP FOREIGN KEY FK_7ADE9A79DE13F470');
        $this->addSql('ALTER TABLE VSCAT_ProductCategories DROP FOREIGN KEY FK_7ADE9A79727ACA70');
        $this->addSql('ALTER TABLE VSCAT_ProductFiles DROP FOREIGN KEY FK_F4F29C927E3C61F9');
        $this->addSql('ALTER TABLE VSCAT_ProductPictures DROP FOREIGN KEY FK_3A0B8B937E3C61F9');
        $this->addSql('ALTER TABLE VSCAT_Product_Associations DROP FOREIGN KEY FK_D832974EFB9C8A5');
        $this->addSql('ALTER TABLE VSCAT_Product_Associations DROP FOREIGN KEY FK_D8329744584665A');
        $this->addSql('ALTER TABLE VSCAT_Product_Categories DROP FOREIGN KEY FK_FA8937394584665A');
        $this->addSql('ALTER TABLE VSCAT_Product_Categories DROP FOREIGN KEY FK_FA89373912469DE2');
        $this->addSql('ALTER TABLE VSCAT_Products DROP FOREIGN KEY FK_D8F34E8C38248176');
        $this->addSql('ALTER TABLE VSGP_GameRooms DROP FOREIGN KEY FK_A1C04365E48FD905');
        $this->addSql('ALTER TABLE VSGP_GameRooms_Players DROP FOREIGN KEY FK_2CFCF2EEE48FD905');
        $this->addSql('ALTER TABLE VSGP_GameRooms_Players DROP FOREIGN KEY FK_2CFCF2EE99E6F5DF');
        $this->addSql('ALTER TABLE VSPAY_Adjustments DROP FOREIGN KEY FK_55CA71E2E415FB15');
        $this->addSql('ALTER TABLE VSPAY_Adjustments DROP FOREIGN KEY FK_55CA71E28D9F6D38');
        $this->addSql('ALTER TABLE VSPAY_CustomerGroups DROP FOREIGN KEY FK_8D3A9BC4DE13F470');
        $this->addSql('ALTER TABLE VSPAY_ExchangeRate DROP FOREIGN KEY FK_1401B615B3FD5856');
        $this->addSql('ALTER TABLE VSPAY_ExchangeRate DROP FOREIGN KEY FK_1401B6152A76BEED');
        $this->addSql('ALTER TABLE VSPAY_GatewayConfig DROP FOREIGN KEY FK_BDE8BA6938248176');
        $this->addSql('ALTER TABLE VSPAY_Order DROP FOREIGN KEY FK_87954502A76ED395');
        $this->addSql('ALTER TABLE VSPAY_Order DROP FOREIGN KEY FK_879545025AA1164F');
        $this->addSql('ALTER TABLE VSPAY_Order DROP FOREIGN KEY FK_879545024C3A3BB');
        $this->addSql('ALTER TABLE VSPAY_Order DROP FOREIGN KEY FK_8795450217B24436');
        $this->addSql('ALTER TABLE VSPAY_OrderItem DROP FOREIGN KEY FK_1C9B655C9A1887DC');
        $this->addSql('ALTER TABLE VSPAY_OrderItem DROP FOREIGN KEY FK_1C9B655C8D9F6D38');
        $this->addSql('ALTER TABLE VSPAY_OrderItem DROP FOREIGN KEY FK_1C9B655C4584665A');
        $this->addSql('ALTER TABLE VSPAY_PaymentMethod DROP FOREIGN KEY FK_1CCD1B9F577F8E00');
        $this->addSql('ALTER TABLE VSPAY_PromotionActions DROP FOREIGN KEY FK_FEEF777139DF194');
        $this->addSql('ALTER TABLE VSPAY_PromotionCoupons DROP FOREIGN KEY FK_FFC21780139DF194');
        $this->addSql('ALTER TABLE VSPAY_PromotionRules DROP FOREIGN KEY FK_9D727099139DF194');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Applications DROP FOREIGN KEY FK_1D3F36D53E030ACD');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Applications DROP FOREIGN KEY FK_1D3F36D5139DF194');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Orders DROP FOREIGN KEY FK_DEAB205F8D9F6D38');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Orders DROP FOREIGN KEY FK_DEAB205F139DF194');
        $this->addSql('ALTER TABLE VSUS_NewsletterSubscriptions DROP FOREIGN KEY FK_E521F0DCF03423AE');
        $this->addSql('ALTER TABLE VSUS_NewsletterSubscriptions DROP FOREIGN KEY FK_E521F0DCA76ED395');
        $this->addSql('ALTER TABLE VSUS_PayedServiceSubscriptionPeriods DROP FOREIGN KEY FK_1018A6BE5139FC0A');
        $this->addSql('ALTER TABLE VSUS_PayedServicesAttributes DROP FOREIGN KEY FK_685989135139FC0A');
        $this->addSql('DROP TABLE VSCAT_AssociationTypes');
        $this->addSql('DROP TABLE VSCAT_PricingPlanCategories');
        $this->addSql('DROP TABLE VSCAT_PricingPlanSubscriptions');
        $this->addSql('DROP TABLE VSCAT_PricingPlans');
        $this->addSql('DROP TABLE VSCAT_ProductAssociations');
        $this->addSql('DROP TABLE VSCAT_ProductCategories');
        $this->addSql('DROP TABLE VSCAT_ProductFiles');
        $this->addSql('DROP TABLE VSCAT_ProductPictures');
        $this->addSql('DROP TABLE VSCAT_Product_Associations');
        $this->addSql('DROP TABLE VSCAT_Product_Categories');
        $this->addSql('DROP TABLE VSCAT_Products');
        $this->addSql('DROP TABLE VSGP_GameRooms');
        $this->addSql('DROP TABLE VSGP_GameRooms_Players');
        $this->addSql('DROP TABLE VSPAY_Adjustments');
        $this->addSql('DROP TABLE VSPAY_Currency');
        $this->addSql('DROP TABLE VSPAY_CustomerGroups');
        $this->addSql('DROP TABLE VSPAY_ExchangeRate');
        $this->addSql('DROP TABLE VSPAY_GatewayConfig');
        $this->addSql('DROP TABLE VSPAY_Order');
        $this->addSql('DROP TABLE VSPAY_OrderItem');
        $this->addSql('DROP TABLE VSPAY_Payment');
        $this->addSql('DROP TABLE VSPAY_PaymentMethod');
        $this->addSql('DROP TABLE VSPAY_PaymentTokens');
        $this->addSql('DROP TABLE VSPAY_PromotionActions');
        $this->addSql('DROP TABLE VSPAY_PromotionCoupons');
        $this->addSql('DROP TABLE VSPAY_PromotionRules');
        $this->addSql('DROP TABLE VSPAY_Promotion_Applications');
        $this->addSql('DROP TABLE VSPAY_Promotion_Orders');
        $this->addSql('DROP TABLE VSPAY_Promotions');
        $this->addSql('DROP TABLE VSUS_MailchimpAudiences');
        $this->addSql('DROP TABLE VSUS_NewsletterSubscriptions');
        $this->addSql('DROP TABLE VSUS_PayedServiceSubscriptionPeriods');
        $this->addSql('DROP TABLE VSUS_PayedServices');
        $this->addSql('DROP TABLE VSUS_PayedServicesAttributes');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id maintenance_page_id  INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id ) REFERENCES VSCMS_Pages (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id )');
        $this->addSql('ALTER TABLE VSGP_GamePlayers ADD elo INT DEFAULT NULL, ADD game_count INT DEFAULT NULL, ADD gold INT DEFAULT NULL, ADD last_free_gold DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP name, DROP color, CHANGE type type ENUM(\'computer\', \'user\')');
        $this->addSql('DROP INDEX IDX_7C2EE794C1D50FBC ON VSGP_GameSessions');
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD game_id INT DEFAULT NULL, ADD winner VARCHAR(40) DEFAULT NULL, DROP game_room_id');
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD CONSTRAINT FK_7C2EE794E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_Games (id)');
        $this->addSql('CREATE INDEX IDX_7C2EE794E48FD905 ON VSGP_GameSessions (game_id)');
        $this->addSql('DROP INDEX IDX_CAFDCD03D2919A68 ON VSUM_Users');
        $this->addSql('ALTER TABLE VSUM_Users DROP customer_group_id, DROP payment_details');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title ENUM(\'mr\', \'mrs\', \'miss\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VSCAT_AssociationTypes (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, name VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, association_strategy VARCHAR(255) CHARACTER SET utf8 DEFAULT \'strategy_associated\' NOT NULL COLLATE `utf8_unicode_ci`, UNIQUE INDEX UNIQ_7F20F79077153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_PricingPlanCategories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, taxon_id INT DEFAULT NULL, INDEX IDX_10C2B955727ACA70 (parent_id), UNIQUE INDEX UNIQ_10C2B955DE13F470 (taxon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_PricingPlanSubscriptions (id INT AUTO_INCREMENT NOT NULL, pricing_plan_id INT DEFAULT NULL, user_id INT DEFAULT NULL, currency_id INT DEFAULT NULL, recurring_payment TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, expires_at DATETIME DEFAULT NULL COMMENT \'Is Updated when create a  New payment for this subscription.\', gateway_attributes JSON DEFAULT NULL, active TINYINT(1) DEFAULT 0 NOT NULL COMMENT \'One Active Subscription for an User and for PaidService. Wnen the Payment succeed set active true and set active false for previous active for this paid service.\', price NUMERIC(8, 2) NOT NULL, INDEX IDX_EA3E01A029628C71 (pricing_plan_id), INDEX IDX_EA3E01A038248176 (currency_id), INDEX IDX_EA3E01A0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_PricingPlans (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, category_id INT NOT NULL, paid_service_id INT NOT NULL, active TINYINT(1) NOT NULL, title VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, premium TINYINT(1) NOT NULL, discount NUMERIC(8, 2) DEFAULT NULL, price NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL, gateway_attributes JSON DEFAULT NULL, INDEX IDX_615E6C0512469DE2 (category_id), INDEX IDX_615E6C0538248176 (currency_id), INDEX IDX_615E6C0587FFD8A7 (paid_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_ProductAssociations (id INT AUTO_INCREMENT NOT NULL, association_type_id INT NOT NULL, product_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_559D39734584665A (product_id), INDEX IDX_559D3973B1E1C39 (association_type_id), UNIQUE INDEX product_association_idx (product_id, association_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_ProductCategories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, taxon_id INT DEFAULT NULL, INDEX IDX_7ADE9A79727ACA70 (parent_id), UNIQUE INDEX UNIQ_7ADE9A79DE13F470 (taxon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_ProductFiles (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, path VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, original_name VARCHAR(255) CHARACTER SET utf8 DEFAULT \'\' NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'The Original Name of the File.\', code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_F4F29C927E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_ProductPictures (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, path VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, original_name VARCHAR(255) CHARACTER SET utf8 DEFAULT \'\' NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'The Original Name of the File.\', code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_3A0B8B937E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_Product_Associations (association_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_D8329744584665A (product_id), INDEX IDX_D832974EFB9C8A5 (association_id), PRIMARY KEY(association_id, product_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_Product_Categories (product_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_FA89373912469DE2 (category_id), INDEX IDX_FA8937394584665A (product_id), PRIMARY KEY(product_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSCAT_Products (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, published TINYINT(1) NOT NULL, slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, name VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, price NUMERIC(8, 2) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, in_stock INT DEFAULT 0 NOT NULL, tags VARCHAR(255) CHARACTER SET utf8 DEFAULT \'\' COLLATE `utf8_unicode_ci`, INDEX IDX_D8F34E8C38248176 (currency_id), UNIQUE INDEX UNIQ_D8F34E8C989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSGP_GameRooms (id INT AUTO_INCREMENT NOT NULL, game_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, is_playing TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_A1C04365E48FD905 (game_id), UNIQUE INDEX UNIQ_A1C04365989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSGP_GameRooms_Players (game_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_2CFCF2EE99E6F5DF (player_id), INDEX IDX_2CFCF2EEE48FD905 (game_id), PRIMARY KEY(game_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_Adjustments (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, order_item_id INT DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, label VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, amount INT NOT NULL, is_neutral TINYINT(1) NOT NULL, is_locked TINYINT(1) NOT NULL, origin_code VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, details JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_55CA71E28D9F6D38 (order_id), INDEX IDX_55CA71E2E415FB15 (order_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_Currency (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(3) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8C67285577153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_CustomerGroups (id INT AUTO_INCREMENT NOT NULL, taxon_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D3A9BC4DE13F470 (taxon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_ExchangeRate (id INT AUTO_INCREMENT NOT NULL, source_currency INT NOT NULL, target_currency INT NOT NULL, ratio NUMERIC(10, 5) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_1401B6152A76BEED (source_currency), INDEX IDX_1401B615B3FD5856 (target_currency), UNIQUE INDEX UNIQ_1401B6152A76BEEDB3FD5856 (source_currency, target_currency), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_GatewayConfig (id INT AUTO_INCREMENT NOT NULL, currency_id INT DEFAULT NULL, gateway_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, factory_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, config JSON NOT NULL, title VARCHAR(255) CHARACTER SET utf8 DEFAULT \'\' NOT NULL COLLATE `utf8_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, use_sandbox TINYINT(1) NOT NULL, sandbox_config JSON DEFAULT NULL, INDEX IDX_BDE8BA6938248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_Order (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, payment_method_id INT DEFAULT NULL, payment_id INT DEFAULT NULL, promotion_coupon_id INT DEFAULT NULL, total_amount DOUBLE PRECISION NOT NULL, currency_code VARCHAR(8) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, status VARCHAR(32) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'NEED THIS BECAUSE ORDER SHOULD BE CREATED BEFORE THE PAYMENT IS PRAPARED AND DONE.\', session_id VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, recurring_payment TINYINT(1) DEFAULT 0 NOT NULL, items_total INT NOT NULL, adjustments_total INT NOT NULL, total INT NOT NULL, INDEX IDX_8795450217B24436 (promotion_coupon_id), INDEX IDX_879545025AA1164F (payment_method_id), INDEX IDX_87954502A76ED395 (user_id), UNIQUE INDEX UNIQ_879545024C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_OrderItem (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, subscription_id INT DEFAULT NULL, product_id INT DEFAULT NULL, payable_object_type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, price NUMERIC(8, 2) NOT NULL, currency_code VARCHAR(8) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, qty INT DEFAULT 1 NOT NULL, adjustments_total INT NOT NULL, total INT NOT NULL, product_name LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_1C9B655C4584665A (product_id), INDEX IDX_1C9B655C8D9F6D38 (order_id), INDEX IDX_1C9B655C9A1887DC (subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_Payment (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, client_email VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, client_id VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, total_amount INT DEFAULT NULL, currency_code VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, details JSON NOT NULL, real_amount NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL COMMENT \'Need this for Real (Human Readable) Amount.\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_PaymentMethod (id INT AUTO_INCREMENT NOT NULL, gateway_id INT DEFAULT NULL, slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, name VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, active TINYINT(1) NOT NULL, INDEX IDX_1CCD1B9F577F8E00 (gateway_id), UNIQUE INDEX UNIQ_1CCD1B9F989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_PaymentTokens (hash VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, details LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:object)\', after_url LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, target_url LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, gateway_name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(hash)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_PromotionActions (id INT AUTO_INCREMENT NOT NULL, promotion_id INT DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, configuration LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', INDEX IDX_FEEF777139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_PromotionCoupons (id INT AUTO_INCREMENT NOT NULL, promotion_id INT DEFAULT NULL, code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, usage_limit INT DEFAULT NULL, used INT NOT NULL, expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_FFC21780139DF194 (promotion_id), UNIQUE INDEX UNIQ_FFC2178077153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_PromotionRules (id INT AUTO_INCREMENT NOT NULL, promotion_id INT DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, configuration LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', INDEX IDX_9D727099139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_Promotion_Applications (promotion_id INT NOT NULL, application_id INT NOT NULL, INDEX IDX_1D3F36D5139DF194 (promotion_id), INDEX IDX_1D3F36D53E030ACD (application_id), PRIMARY KEY(promotion_id, application_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_Promotion_Orders (order_id INT NOT NULL, promotion_id INT NOT NULL, INDEX IDX_DEAB205F139DF194 (promotion_id), INDEX IDX_DEAB205F8D9F6D38 (order_id), PRIMARY KEY(order_id, promotion_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSPAY_Promotions (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, priority INT NOT NULL, exclusive TINYINT(1) NOT NULL, usage_limit INT DEFAULT NULL, used INT NOT NULL, coupon_based TINYINT(1) NOT NULL, starts_at DATETIME DEFAULT NULL, ends_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_A3DFF5C077153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSUS_MailchimpAudiences (id INT AUTO_INCREMENT NOT NULL, audience_id VARCHAR(16) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSUS_NewsletterSubscriptions (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, mailchimp_audience_id INT NOT NULL, user_email VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, date DATETIME NOT NULL, INDEX IDX_E521F0DCA76ED395 (user_id), INDEX IDX_E521F0DCF03423AE (mailchimp_audience_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSUS_PayedServiceSubscriptionPeriods (id INT AUTO_INCREMENT NOT NULL, payed_service_id INT DEFAULT NULL, subscription_period VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, title VARCHAR(64) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, paid_service_period_code VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'The Code Used To Find The Subscription Period in Fixture Factory when Creating Pricing Plans.\', INDEX IDX_1018A6BE5139FC0A (payed_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSUS_PayedServices (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, description LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, subscription_code VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'Subscription Code Group Payed Services for an identical parameter but with differents levels(priority).\', subscription_priority INT NOT NULL COMMENT \'Subscription Priority is the level of a Subscription Code.\', UNIQUE INDEX subscription_idx (subscription_code, subscription_priority), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE VSUS_PayedServicesAttributes (id INT AUTO_INCREMENT NOT NULL, payed_service_id INT DEFAULT NULL, name VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, value VARCHAR(64) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, INDEX IDX_685989135139FC0A (payed_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories ADD CONSTRAINT FK_10C2B955DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanCategories ADD CONSTRAINT FK_10C2B955727ACA70 FOREIGN KEY (parent_id) REFERENCES VSCAT_PricingPlanCategories (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions ADD CONSTRAINT FK_EA3E01A0A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions ADD CONSTRAINT FK_EA3E01A038248176 FOREIGN KEY (currency_id) REFERENCES VSPAY_Currency (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_PricingPlanSubscriptions ADD CONSTRAINT FK_EA3E01A029628C71 FOREIGN KEY (pricing_plan_id) REFERENCES VSCAT_PricingPlans (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_PricingPlans ADD CONSTRAINT FK_615E6C0587FFD8A7 FOREIGN KEY (paid_service_id) REFERENCES VSUS_PayedServiceSubscriptionPeriods (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSCAT_PricingPlans ADD CONSTRAINT FK_615E6C0538248176 FOREIGN KEY (currency_id) REFERENCES VSPAY_Currency (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_PricingPlans ADD CONSTRAINT FK_615E6C0512469DE2 FOREIGN KEY (category_id) REFERENCES VSCAT_PricingPlanCategories (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSCAT_ProductAssociations ADD CONSTRAINT FK_559D3973B1E1C39 FOREIGN KEY (association_type_id) REFERENCES VSCAT_AssociationTypes (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSCAT_ProductAssociations ADD CONSTRAINT FK_559D39734584665A FOREIGN KEY (product_id) REFERENCES VSCAT_Products (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSCAT_ProductCategories ADD CONSTRAINT FK_7ADE9A79DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_ProductCategories ADD CONSTRAINT FK_7ADE9A79727ACA70 FOREIGN KEY (parent_id) REFERENCES VSCAT_ProductCategories (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_ProductFiles ADD CONSTRAINT FK_F4F29C927E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSCAT_Products (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_ProductPictures ADD CONSTRAINT FK_3A0B8B937E3C61F9 FOREIGN KEY (owner_id) REFERENCES VSCAT_Products (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_Product_Associations ADD CONSTRAINT FK_D832974EFB9C8A5 FOREIGN KEY (association_id) REFERENCES VSCAT_ProductAssociations (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSCAT_Product_Associations ADD CONSTRAINT FK_D8329744584665A FOREIGN KEY (product_id) REFERENCES VSCAT_Products (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSCAT_Product_Categories ADD CONSTRAINT FK_FA8937394584665A FOREIGN KEY (product_id) REFERENCES VSCAT_Products (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_Product_Categories ADD CONSTRAINT FK_FA89373912469DE2 FOREIGN KEY (category_id) REFERENCES VSCAT_ProductCategories (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSCAT_Products ADD CONSTRAINT FK_D8F34E8C38248176 FOREIGN KEY (currency_id) REFERENCES VSPAY_Currency (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSGP_GameRooms ADD CONSTRAINT FK_A1C04365E48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_Games (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSGP_GameRooms_Players ADD CONSTRAINT FK_2CFCF2EEE48FD905 FOREIGN KEY (game_id) REFERENCES VSGP_GameRooms (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSGP_GameRooms_Players ADD CONSTRAINT FK_2CFCF2EE99E6F5DF FOREIGN KEY (player_id) REFERENCES VSGP_GamePlayers (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_Adjustments ADD CONSTRAINT FK_55CA71E2E415FB15 FOREIGN KEY (order_item_id) REFERENCES VSPAY_OrderItem (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSPAY_Adjustments ADD CONSTRAINT FK_55CA71E28D9F6D38 FOREIGN KEY (order_id) REFERENCES VSPAY_Order (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSPAY_CustomerGroups ADD CONSTRAINT FK_8D3A9BC4DE13F470 FOREIGN KEY (taxon_id) REFERENCES VSAPP_Taxons (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_ExchangeRate ADD CONSTRAINT FK_1401B615B3FD5856 FOREIGN KEY (target_currency) REFERENCES VSPAY_Currency (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSPAY_ExchangeRate ADD CONSTRAINT FK_1401B6152A76BEED FOREIGN KEY (source_currency) REFERENCES VSPAY_Currency (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSPAY_GatewayConfig ADD CONSTRAINT FK_BDE8BA6938248176 FOREIGN KEY (currency_id) REFERENCES VSPAY_Currency (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_Order ADD CONSTRAINT FK_87954502A76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_Order ADD CONSTRAINT FK_879545025AA1164F FOREIGN KEY (payment_method_id) REFERENCES VSPAY_PaymentMethod (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_Order ADD CONSTRAINT FK_879545024C3A3BB FOREIGN KEY (payment_id) REFERENCES VSPAY_Payment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_Order ADD CONSTRAINT FK_8795450217B24436 FOREIGN KEY (promotion_coupon_id) REFERENCES VSPAY_PromotionCoupons (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_OrderItem ADD CONSTRAINT FK_1C9B655C9A1887DC FOREIGN KEY (subscription_id) REFERENCES VSCAT_PricingPlanSubscriptions (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_OrderItem ADD CONSTRAINT FK_1C9B655C8D9F6D38 FOREIGN KEY (order_id) REFERENCES VSPAY_Order (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_OrderItem ADD CONSTRAINT FK_1C9B655C4584665A FOREIGN KEY (product_id) REFERENCES VSCAT_Products (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_PaymentMethod ADD CONSTRAINT FK_1CCD1B9F577F8E00 FOREIGN KEY (gateway_id) REFERENCES VSPAY_GatewayConfig (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_PromotionActions ADD CONSTRAINT FK_FEEF777139DF194 FOREIGN KEY (promotion_id) REFERENCES VSPAY_Promotions (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_PromotionCoupons ADD CONSTRAINT FK_FFC21780139DF194 FOREIGN KEY (promotion_id) REFERENCES VSPAY_Promotions (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_PromotionRules ADD CONSTRAINT FK_9D727099139DF194 FOREIGN KEY (promotion_id) REFERENCES VSPAY_Promotions (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Applications ADD CONSTRAINT FK_1D3F36D53E030ACD FOREIGN KEY (application_id) REFERENCES VSAPP_Applications (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Applications ADD CONSTRAINT FK_1D3F36D5139DF194 FOREIGN KEY (promotion_id) REFERENCES VSPAY_Promotions (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Orders ADD CONSTRAINT FK_DEAB205F8D9F6D38 FOREIGN KEY (order_id) REFERENCES VSPAY_Order (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE VSPAY_Promotion_Orders ADD CONSTRAINT FK_DEAB205F139DF194 FOREIGN KEY (promotion_id) REFERENCES VSPAY_Promotions (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSUS_NewsletterSubscriptions ADD CONSTRAINT FK_E521F0DCF03423AE FOREIGN KEY (mailchimp_audience_id) REFERENCES VSUS_MailchimpAudiences (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSUS_NewsletterSubscriptions ADD CONSTRAINT FK_E521F0DCA76ED395 FOREIGN KEY (user_id) REFERENCES VSUM_Users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSUS_PayedServiceSubscriptionPeriods ADD CONSTRAINT FK_1018A6BE5139FC0A FOREIGN KEY (payed_service_id) REFERENCES VSUS_PayedServices (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSUS_PayedServicesAttributes ADD CONSTRAINT FK_685989135139FC0A FOREIGN KEY (payed_service_id) REFERENCES VSUS_PayedServices (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VSGP_TempPlayers DROP FOREIGN KEY FK_1CCF81699E6F5DF');
        $this->addSql('ALTER TABLE VSGP_TempPlayers DROP FOREIGN KEY FK_1CCF816E48FD905');
        $this->addSql('DROP TABLE VSGP_TempPlayers');
        $this->addSql('ALTER TABLE VSAPP_Settings DROP FOREIGN KEY FK_4A491FD507FAB6A');
        $this->addSql('DROP INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings');
        $this->addSql('ALTER TABLE VSAPP_Settings CHANGE maintenance_page_id  maintenance_page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE VSAPP_Settings ADD CONSTRAINT FK_4A491FD507FAB6A FOREIGN KEY (maintenance_page_id) REFERENCES VSCMS_Pages (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4A491FD507FAB6A ON VSAPP_Settings (maintenance_page_id)');
        $this->addSql('ALTER TABLE VSGP_GamePlayers ADD name VARCHAR(255) NOT NULL, ADD color VARCHAR(255) DEFAULT NULL, DROP elo, DROP game_count, DROP gold, DROP last_free_gold, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE VSGP_GameSessions DROP FOREIGN KEY FK_7C2EE794E48FD905');
        $this->addSql('DROP INDEX IDX_7C2EE794E48FD905 ON VSGP_GameSessions');
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD game_room_id INT NOT NULL, DROP game_id, DROP winner');
        $this->addSql('ALTER TABLE VSGP_GameSessions ADD CONSTRAINT FK_7C2EE794C1D50FBC FOREIGN KEY (game_room_id) REFERENCES VSGP_GameRooms (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_7C2EE794C1D50FBC ON VSGP_GameSessions (game_room_id)');
        $this->addSql('ALTER TABLE VSUM_Users ADD customer_group_id INT DEFAULT NULL, ADD payment_details JSON NOT NULL');
        $this->addSql('ALTER TABLE VSUM_Users ADD CONSTRAINT FK_CAFDCD03D2919A68 FOREIGN KEY (customer_group_id) REFERENCES VSPAY_CustomerGroups (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_CAFDCD03D2919A68 ON VSUM_Users (customer_group_id)');
        $this->addSql('ALTER TABLE VSUM_UsersInfo CHANGE title title VARCHAR(255) DEFAULT NULL');
    }
}
