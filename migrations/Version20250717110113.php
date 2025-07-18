<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717110113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE course_tag (course_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_760531B1591CC992 (course_id), INDEX IDX_760531B1BAD26311 (tag_id), PRIMARY KEY(course_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_tag ADD CONSTRAINT FK_760531B1591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_tag ADD CONSTRAINT FK_760531B1BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE course_tag DROP FOREIGN KEY FK_760531B1591CC992
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_tag DROP FOREIGN KEY FK_760531B1BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE course_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tag
        SQL);
    }
}
