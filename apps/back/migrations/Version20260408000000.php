<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260408000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: all tables for apuntate application';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql("CREATE TABLE users (id UUID NOT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, date_start DATE NOT NULL, date_end DATE DEFAULT NULL, roles TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)");
        $this->addSql("COMMENT ON COLUMN users.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN users.roles IS '(DC2Type:array)'");

        $this->addSql("CREATE TABLE organizations (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_427C1C7F989D9B62 ON organizations (slug)");
        $this->addSql("COMMENT ON COLUMN organizations.id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE organization_members (id UUID NOT NULL, organization_id UUID NOT NULL, user_id UUID NOT NULL, role VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_88725ABC32C8A3DE ON organization_members (organization_id)");
        $this->addSql("CREATE INDEX IDX_88725ABCA76ED395 ON organization_members (user_id)");
        $this->addSql("CREATE UNIQUE INDEX unique_org_user ON organization_members (organization_id, user_id)");
        $this->addSql("COMMENT ON COLUMN organization_members.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN organization_members.organization_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN organization_members.user_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE speciality (id UUID NOT NULL, name VARCHAR(255) NOT NULL, abbreviation VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("COMMENT ON COLUMN speciality.id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE requirement (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("COMMENT ON COLUMN requirement.id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE component (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("COMMENT ON COLUMN component.id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE component_requirement (component_id UUID NOT NULL, requirement_id UUID NOT NULL, PRIMARY KEY(component_id, requirement_id))");
        $this->addSql("CREATE INDEX IDX_812638D3E2ABAFFF ON component_requirement (component_id)");
        $this->addSql("CREATE INDEX IDX_812638D37B576F77 ON component_requirement (requirement_id)");
        $this->addSql("COMMENT ON COLUMN component_requirement.component_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN component_requirement.requirement_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE unit (id UUID NOT NULL, speciality_id UUID DEFAULT NULL, organization_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, identifier VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_DCBB0C533B5A08D7 ON unit (speciality_id)");
        $this->addSql("CREATE INDEX IDX_DCBB0C5332C8A3DE ON unit (organization_id)");
        $this->addSql("COMMENT ON COLUMN unit.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN unit.speciality_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN unit.organization_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE unit_component (id UUID NOT NULL, unit_id UUID DEFAULT NULL, component_id UUID DEFAULT NULL, quantity INT NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_578A6DD5F8BD700D ON unit_component (unit_id)");
        $this->addSql("CREATE INDEX IDX_578A6DD5E2ABAFFF ON unit_component (component_id)");
        $this->addSql("COMMENT ON COLUMN unit_component.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN unit_component.unit_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN unit_component.component_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE service (id UUID NOT NULL, organization_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, date_start TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, date_end TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, date_place TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_E19D9AD232C8A3DE ON service (organization_id)");
        $this->addSql("COMMENT ON COLUMN service.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN service.organization_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE service_unit (service_id UUID NOT NULL, unit_id UUID NOT NULL, PRIMARY KEY(service_id, unit_id))");
        $this->addSql("CREATE INDEX IDX_12F8B8BFED5CA9E6 ON service_unit (service_id)");
        $this->addSql("CREATE INDEX IDX_12F8B8BFF8BD700D ON service_unit (unit_id)");
        $this->addSql("COMMENT ON COLUMN service_unit.service_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN service_unit.unit_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE gap (id UUID NOT NULL, service_id UUID DEFAULT NULL, user_id UUID DEFAULT NULL, unit_component_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_9E3A2F6DED5CA9E6 ON gap (service_id)");
        $this->addSql("CREATE INDEX IDX_9E3A2F6DA76ED395 ON gap (user_id)");
        $this->addSql("CREATE INDEX IDX_9E3A2F6D303D4D68 ON gap (unit_component_id)");
        $this->addSql("COMMENT ON COLUMN gap.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN gap.service_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN gap.user_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN gap.unit_component_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE alerts (id UUID NOT NULL, recipient_id UUID NOT NULL, service_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, resume TEXT NOT NULL, type VARCHAR(50) NOT NULL, read BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_F77AC06BE92F8F78 ON alerts (recipient_id)");
        $this->addSql("CREATE INDEX IDX_F77AC06BED5CA9E6 ON alerts (service_id)");
        $this->addSql("COMMENT ON COLUMN alerts.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN alerts.recipient_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN alerts.service_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE refresh_tokens (id INT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)");

        $this->addSql("CREATE TABLE user_requirement (user_id UUID NOT NULL, requirement_id UUID NOT NULL, PRIMARY KEY(user_id, requirement_id))");
        $this->addSql("CREATE INDEX IDX_72249CC5A76ED395 ON user_requirement (user_id)");
        $this->addSql("CREATE INDEX IDX_72249CC57B576F77 ON user_requirement (requirement_id)");
        $this->addSql("COMMENT ON COLUMN user_requirement.user_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN user_requirement.requirement_id IS '(DC2Type:uuid)'");

        $this->addSql("CREATE TABLE user_speciality (id UUID NOT NULL, speciality_id UUID NOT NULL, user_id UUID NOT NULL, date_start DATE NOT NULL, date_end DATE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_54B066623B5A08D7 ON user_speciality (speciality_id)");
        $this->addSql("CREATE INDEX IDX_54B06662A76ED395 ON user_speciality (user_id)");
        $this->addSql("COMMENT ON COLUMN user_speciality.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN user_speciality.speciality_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN user_speciality.user_id IS '(DC2Type:uuid)'");

        // Foreign keys
        $this->addSql("ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06BE92F8F78 FOREIGN KEY (recipient_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06BED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE component_requirement ADD CONSTRAINT FK_812638D3E2ABAFFF FOREIGN KEY (component_id) REFERENCES component (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE component_requirement ADD CONSTRAINT FK_812638D37B576F77 FOREIGN KEY (requirement_id) REFERENCES requirement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE gap ADD CONSTRAINT FK_9E3A2F6DED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE gap ADD CONSTRAINT FK_9E3A2F6DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE gap ADD CONSTRAINT FK_9E3A2F6D303D4D68 FOREIGN KEY (unit_component_id) REFERENCES unit_component (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE organization_members ADD CONSTRAINT FK_88725ABC32C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE organization_members ADD CONSTRAINT FK_88725ABCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE service ADD CONSTRAINT FK_E19D9AD232C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE service_unit ADD CONSTRAINT FK_12F8B8BFED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE service_unit ADD CONSTRAINT FK_12F8B8BFF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C533B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C5332C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE unit_component ADD CONSTRAINT FK_578A6DD5F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE unit_component ADD CONSTRAINT FK_578A6DD5E2ABAFFF FOREIGN KEY (component_id) REFERENCES component (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_requirement ADD CONSTRAINT FK_72249CC5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_requirement ADD CONSTRAINT FK_72249CC57B576F77 FOREIGN KEY (requirement_id) REFERENCES requirement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_speciality ADD CONSTRAINT FK_54B066623B5A08D7 FOREIGN KEY (speciality_id) REFERENCES speciality (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE user_speciality ADD CONSTRAINT FK_54B06662A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS user_speciality');
        $this->addSql('DROP TABLE IF EXISTS user_requirement');
        $this->addSql('DROP TABLE IF EXISTS refresh_tokens');
        $this->addSql('DROP TABLE IF EXISTS alerts');
        $this->addSql('DROP TABLE IF EXISTS gap');
        $this->addSql('DROP TABLE IF EXISTS service_unit');
        $this->addSql('DROP TABLE IF EXISTS service');
        $this->addSql('DROP TABLE IF EXISTS unit_component');
        $this->addSql('DROP TABLE IF EXISTS unit');
        $this->addSql('DROP TABLE IF EXISTS component_requirement');
        $this->addSql('DROP TABLE IF EXISTS component');
        $this->addSql('DROP TABLE IF EXISTS requirement');
        $this->addSql('DROP TABLE IF EXISTS speciality');
        $this->addSql('DROP TABLE IF EXISTS organization_members');
        $this->addSql('DROP TABLE IF EXISTS organizations');
        $this->addSql('DROP TABLE IF EXISTS users');
        $this->addSql('DROP SEQUENCE IF EXISTS refresh_tokens_id_seq');
    }
}
