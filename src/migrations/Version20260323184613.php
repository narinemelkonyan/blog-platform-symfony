<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260323184613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_tags (article_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_DFFE13277294869C (article_id), INDEX IDX_DFFE1327BAD26311 (tag_id), PRIMARY KEY (article_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug), INDEX IDX_3AF34668727ACA70 (parent_id), INDEX idx_category_slug (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, is_approved TINYINT DEFAULT 0 NOT NULL, article_id INT NOT NULL, author_id INT NOT NULL, parent_id INT DEFAULT NULL, INDEX IDX_5F9E962A7294869C (article_id), INDEX IDX_5F9E962AF675F31B (author_id), INDEX IDX_5F9E962A727ACA70 (parent_id), INDEX idx_comment_created_at (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_49CA4E7DA76ED395 (user_id), INDEX IDX_49CA4E7D7294869C (article_id), UNIQUE INDEX unique_user_article_like (user_id, article_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, slug VARCHAR(60) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_6FBC9426989D9B62 (slug), INDEX idx_tag_slug (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article_tags ADD CONSTRAINT FK_DFFE13277294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tags ADD CONSTRAINT FK_DFFE1327BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A727ACA70 FOREIGN KEY (parent_id) REFERENCES comments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7D7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article ADD status VARCHAR(20) DEFAULT \'draft\' NOT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_article_slug ON article (slug)');
        $this->addSql('CREATE INDEX idx_article_status ON article (status)');
        $this->addSql('ALTER TABLE article RENAME INDEX idx_23a0e668b8e8428 TO idx_article_created_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_tags DROP FOREIGN KEY FK_DFFE13277294869C');
        $this->addSql('ALTER TABLE article_tags DROP FOREIGN KEY FK_DFFE1327BAD26311');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A7294869C');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AF675F31B');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A727ACA70');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DA76ED395');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7D7294869C');
        $this->addSql('DROP TABLE article_tags');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE likes');
        $this->addSql('DROP TABLE tags');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B');
        $this->addSql('DROP INDEX idx_article_slug ON article');
        $this->addSql('DROP INDEX idx_article_status ON article');
        $this->addSql('ALTER TABLE article DROP status');
        $this->addSql('ALTER TABLE article RENAME INDEX idx_article_created_at TO IDX_23A0E668B8E8428');
    }
}
