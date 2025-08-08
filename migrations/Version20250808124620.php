<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250808124620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9853CD175
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_tag DROP FOREIGN KEY FK_760531B1591CC992
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_tag DROP FOREIGN KEY FK_760531B1BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_course_status DROP FOREIGN KEY FK_3BB60901591CC992
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_course_status DROP FOREIGN KEY FK_3BB60901A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE course
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE course_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_course_status
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, youtube_video_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_169E6FB9853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE course_tag (course_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_760531B1591CC992 (course_id), INDEX IDX_760531B1BAD26311 (tag_id), PRIMARY KEY(course_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_course_status (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, course_id INT NOT NULL, is_done TINYINT(1) NOT NULL, INDEX IDX_3BB60901591CC992 (course_id), INDEX IDX_3BB60901A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course ADD CONSTRAINT FK_169E6FB9853CD175 FOREIGN KEY (quiz_id) REFERENCES quizz (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_tag ADD CONSTRAINT FK_760531B1591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_tag ADD CONSTRAINT FK_760531B1BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_course_status ADD CONSTRAINT FK_3BB60901591CC992 FOREIGN KEY (course_id) REFERENCES course (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_course_status ADD CONSTRAINT FK_3BB60901A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }
}
