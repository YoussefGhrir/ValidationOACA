<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250318231922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avion (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE compagnie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directeur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ministere (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_44118A5B6C6E55B5 (nom), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pilote (id INT AUTO_INCREMENT NOT NULL, adminpilot_id INT DEFAULT NULL, compagnie_id INT DEFAULT NULL, avion_id INT DEFAULT NULL, created_by_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, datebirth DATE DEFAULT NULL, numero VARCHAR(255) DEFAULT NULL, firstdate DATE DEFAULT NULL, validite DATE DEFAULT NULL, datelangue DATE DEFAULT NULL, pays VARCHAR(255) DEFAULT NULL, nationalite VARCHAR(180) DEFAULT NULL, type TINYINT(1) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, datequalif DATE DEFAULT NULL, pdf_number INT DEFAULT NULL, privilegefr VARCHAR(255) DEFAULT NULL, privilegeag VARCHAR(255) DEFAULT NULL, statut TINYINT(1) NOT NULL, datelicence DATE DEFAULT NULL, INDEX IDX_6A3254DD7E21206F (adminpilot_id), INDEX IDX_6A3254DD52FBE437 (compagnie_id), INDEX IDX_6A3254DD80BBB841 (avion_id), INDEX IDX_6A3254DDB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE validation_historique (id INT AUTO_INCREMENT NOT NULL, pilote_id INT DEFAULT NULL, date_delivree DATETIME DEFAULT NULL, date_valide_jusquau DATETIME DEFAULT NULL, INDEX IDX_75E6AC41F510AAE9 (pilote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pilote ADD CONSTRAINT FK_6A3254DD7E21206F FOREIGN KEY (adminpilot_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE pilote ADD CONSTRAINT FK_6A3254DD52FBE437 FOREIGN KEY (compagnie_id) REFERENCES compagnie (id)');
        $this->addSql('ALTER TABLE pilote ADD CONSTRAINT FK_6A3254DD80BBB841 FOREIGN KEY (avion_id) REFERENCES avion (id)');
        $this->addSql('ALTER TABLE pilote ADD CONSTRAINT FK_6A3254DDB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE validation_historique ADD CONSTRAINT FK_75E6AC41F510AAE9 FOREIGN KEY (pilote_id) REFERENCES pilote (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pilote DROP FOREIGN KEY FK_6A3254DD7E21206F');
        $this->addSql('ALTER TABLE pilote DROP FOREIGN KEY FK_6A3254DD52FBE437');
        $this->addSql('ALTER TABLE pilote DROP FOREIGN KEY FK_6A3254DD80BBB841');
        $this->addSql('ALTER TABLE pilote DROP FOREIGN KEY FK_6A3254DDB03A8386');
        $this->addSql('ALTER TABLE validation_historique DROP FOREIGN KEY FK_75E6AC41F510AAE9');
        $this->addSql('DROP TABLE avion');
        $this->addSql('DROP TABLE compagnie');
        $this->addSql('DROP TABLE directeur');
        $this->addSql('DROP TABLE ministere');
        $this->addSql('DROP TABLE pilote');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE validation_historique');
    }
}
