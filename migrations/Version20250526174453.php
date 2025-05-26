<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250526174453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change column "roles" type to JSON in "user" table';
    }

    public function up(Schema $schema): void
    {
        // Correction : conversion explicite avec USING
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER roles TYPE JSON USING roles::json
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER roles SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".roles IS NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Rétablir à TEXT + annotation Doctrine
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER roles TYPE TEXT USING roles::text
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER roles DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".roles IS '(DC2Type:array)'
        SQL);
    }
}