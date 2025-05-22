<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522062214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE article_category DROP CONSTRAINT fk_53a4edaa7294869c
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_category DROP CONSTRAINT fk_53a4edaa12469de2
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article_category
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD category_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_23A0E6612469DE2 ON article (category_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE article_category (article_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(article_id, category_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_53a4edaa12469de2 ON article_category (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_53a4edaa7294869c ON article_category (article_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_category ADD CONSTRAINT fk_53a4edaa7294869c FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_category ADD CONSTRAINT fk_53a4edaa12469de2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP CONSTRAINT FK_23A0E6612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_23A0E6612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP category_id
        SQL);
    }
}
