<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231218213338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, text LONGTEXT DEFAULT NULL, date_commentaire DATETIME DEFAULT NULL, INDEX IDX_8F91ABF071F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name_category VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, theme_id INT NOT NULL, description LONGTEXT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, note INT NOT NULL, INDEX IDX_3BAE0AA759027487 (theme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, date_de_creation DATETIME NOT NULL, INDEX IDX_9775E70812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(100) NOT NULL, pseudo VARCHAR(50) NOT NULL, password VARCHAR(50) NOT NULL, date_inscription DATETIME NOT NULL, role JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF071F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA759027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E70812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF071F7E88B');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA759027487');
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E70812469DE2');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
