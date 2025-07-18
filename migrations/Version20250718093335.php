<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718093335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE quizz_tag (quizz_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_8D32F256BA934BCD (quizz_id), INDEX IDX_8D32F256BAD26311 (tag_id), PRIMARY KEY(quizz_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quizz_tag ADD CONSTRAINT FK_8D32F256BA934BCD FOREIGN KEY (quizz_id) REFERENCES quizz (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quizz_tag ADD CONSTRAINT FK_8D32F256BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE quizz_tag DROP FOREIGN KEY FK_8D32F256BA934BCD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE quizz_tag DROP FOREIGN KEY FK_8D32F256BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE quizz_tag
        SQL);
    }
}
