<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260519103512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_article_status ON article');
        $this->addSql('DROP INDEX idx_article_created_at ON article');
        $this->addSql('CREATE INDEX idx_article_status_created_at ON article (status, created_at)');
        $this->addSql('CREATE INDEX idx_comment_article_parent_approved ON comments (article_id, parent_id, is_approved)');
        $this->addSql('ALTER TABLE comments RENAME INDEX idx_5f9e962af675f31b TO idx_comment_author');
        $this->addSql('ALTER TABLE user ADD pending_email VARCHAR(180) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6497807FE7C ON user (pending_email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_article_status_created_at ON article');
        $this->addSql('CREATE INDEX idx_article_status ON article (status)');
        $this->addSql('CREATE INDEX idx_article_created_at ON article (created_at)');
        $this->addSql('DROP INDEX idx_comment_article_parent_approved ON comments');
        $this->addSql('ALTER TABLE comments RENAME INDEX idx_comment_author TO IDX_5F9E962AF675F31B');
        $this->addSql('DROP INDEX UNIQ_8D93D6497807FE7C ON `user`');
        $this->addSql('ALTER TABLE `user` DROP pending_email');
    }
}
