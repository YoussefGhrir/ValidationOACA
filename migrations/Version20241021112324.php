<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021112324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE validation_historique (id INT AUTO_INCREMENT NOT NULL, pilote_id INT DEFAULT NULL, date_delivree DATETIME DEFAULT NULL, date_valide_jusquau DATETIME DEFAULT NULL, INDEX IDX_75E6AC41F510AAE9 (pilote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE validation_historique ADD CONSTRAINT FK_75E6AC41F510AAE9 FOREIGN KEY (pilote_id) REFERENCES pilote (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE validation_historique DROP FOREIGN KEY FK_75E6AC41F510AAE9');
        $this->addSql('DROP TABLE validation_historique');
    }
}
