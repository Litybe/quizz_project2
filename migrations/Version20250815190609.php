<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250815190609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE map (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE strategy (id INT AUTO_INCREMENT NOT NULL, map_id INT NOT NULL, author_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, side VARCHAR(50) NOT NULL, difficulty VARCHAR(50) NOT NULL, execution LONGTEXT DEFAULT NULL, counters LONGTEXT DEFAULT NULL, is_public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_144645ED53C55F64 (map_id), INDEX IDX_144645EDF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE strategy_tag (strategy_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_A05F4449D5CAD932 (strategy_id), INDEX IDX_A05F4449BAD26311 (tag_id), PRIMARY KEY(strategy_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE strategy_position (id INT AUTO_INCREMENT NOT NULL, strategy_id INT NOT NULL, player_number INT NOT NULL, position_name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, x DOUBLE PRECISION NOT NULL, y DOUBLE PRECISION NOT NULL, role VARCHAR(255) DEFAULT NULL, instructions LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_7D740B6ED5CAD932 (strategy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy ADD CONSTRAINT FK_144645ED53C55F64 FOREIGN KEY (map_id) REFERENCES map (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy ADD CONSTRAINT FK_144645EDF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy_tag ADD CONSTRAINT FK_A05F4449D5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy_tag ADD CONSTRAINT FK_A05F4449BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy_position ADD CONSTRAINT FK_7D740B6ED5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy DROP FOREIGN KEY FK_144645ED53C55F64
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy DROP FOREIGN KEY FK_144645EDF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy_tag DROP FOREIGN KEY FK_A05F4449D5CAD932
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy_tag DROP FOREIGN KEY FK_A05F4449BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy_position DROP FOREIGN KEY FK_7D740B6ED5CAD932
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE map
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE strategy
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE strategy_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE strategy_position
        SQL);
    }
}
