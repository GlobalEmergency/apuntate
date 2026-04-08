<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408143354 extends AbstractMigration
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
        $this->addSql('ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06BE92F8F78 FOREIGN KEY (recipient_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE alerts ADD CONSTRAINT FK_F77AC06BED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ALTER date_start TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER date_end TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER date_place TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA apuntate');
        $this->addSql('ALTER TABLE alerts DROP CONSTRAINT FK_F77AC06BE92F8F78');
        $this->addSql('ALTER TABLE alerts DROP CONSTRAINT FK_F77AC06BED5CA9E6');
        $this->addSql('DROP TABLE alerts');
        $this->addSql('DROP INDEX UNIQ_1483A5E9E7927C74');
        $this->addSql('ALTER TABLE service ALTER date_start TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER date_end TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER date_place TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }
}
