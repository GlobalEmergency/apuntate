<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert service timestamp columns to TIMESTAMP WITH TIME ZONE (existing data treated as UTC)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE service ALTER COLUMN date_start TYPE TIMESTAMP(6) WITH TIME ZONE USING date_start AT TIME ZONE 'UTC'");
        $this->addSql("ALTER TABLE service ALTER COLUMN date_end TYPE TIMESTAMP(6) WITH TIME ZONE USING date_end AT TIME ZONE 'UTC'");
        $this->addSql("ALTER TABLE service ALTER COLUMN date_place TYPE TIMESTAMP(6) WITH TIME ZONE USING date_place AT TIME ZONE 'UTC'");
        $this->addSql("ALTER TABLE service ALTER COLUMN created_at TYPE TIMESTAMP(0) WITH TIME ZONE USING created_at AT TIME ZONE 'UTC'");
        $this->addSql("ALTER TABLE service ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITH TIME ZONE USING updated_at AT TIME ZONE 'UTC'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service ALTER COLUMN date_start TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER COLUMN date_end TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER COLUMN date_place TYPE TIMESTAMP(6) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER COLUMN created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE service ALTER COLUMN updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }
}
