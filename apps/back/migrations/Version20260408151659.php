<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408151659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerts (id UUID NOT NULL, recipient_id UUID NOT NULL, service_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, resume TEXT NOT NULL, type VARCHAR(50) NOT NULL, read BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F77AC06BE92F8F78 ON alerts (recipient_id)');
        $this->addSql('CREATE INDEX IDX_F77AC06BED5CA9E6 ON alerts (service_id)');
        $this->addSql('COMMENT ON COLUMN alerts.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN alerts.recipient_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN alerts.service_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE organization_members (id UUID NOT NULL, organization_id UUID NOT NULL, user_id UUID NOT NULL, role VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_88725ABC32C8A3DE ON organization_members (organization_id)');
        $this->addSql('CREATE INDEX IDX_88725ABCA76ED395 ON organization_members (user_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_org_user ON organization_members (organization_id, user_id)');
        $this->addSql('COMMENT ON COLUMN organization_members.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_members.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_members.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE organizations (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_427C1C7F989D9B62 ON organizations (slug)');
        $this->addSql('COMMENT ON COLUMN organizations.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06BE92F8F78 FOREIGN KEY (recipient_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06BED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_members ADD CONSTRAINT FK_88725ABC32C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_members ADD CONSTRAINT FK_88725ABCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD organization_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE service ALTER date_start TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER date_end TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER date_place TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN service.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD232C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E19D9AD232C8A3DE ON service (organization_id)');
        $this->addSql('ALTER TABLE unit ADD organization_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN unit.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C5332C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DCBB0C5332C8A3DE ON unit (organization_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA apuntate');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD232C8A3DE');
        $this->addSql('ALTER TABLE unit DROP CONSTRAINT FK_DCBB0C5332C8A3DE');
        $this->addSql('ALTER TABLE alerts DROP CONSTRAINT FK_F77AC06BE92F8F78');
        $this->addSql('ALTER TABLE alerts DROP CONSTRAINT FK_F77AC06BED5CA9E6');
        $this->addSql('ALTER TABLE organization_members DROP CONSTRAINT FK_88725ABC32C8A3DE');
        $this->addSql('ALTER TABLE organization_members DROP CONSTRAINT FK_88725ABCA76ED395');
        $this->addSql('DROP TABLE alerts');
        $this->addSql('DROP TABLE organization_members');
        $this->addSql('DROP TABLE organizations');
        $this->addSql('DROP INDEX IDX_DCBB0C5332C8A3DE');
        $this->addSql('ALTER TABLE unit DROP organization_id');
        $this->addSql('DROP INDEX UNIQ_1483A5E9E7927C74');
        $this->addSql('DROP INDEX IDX_E19D9AD232C8A3DE');
        $this->addSql('ALTER TABLE service DROP organization_id');
        $this->addSql('ALTER TABLE service ALTER date_start TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER date_end TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER date_place TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }
}
