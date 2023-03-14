<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314090947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE form_visit_data (id INT AUTO_INCREMENT NOT NULL, task_id INT DEFAULT NULL, customer_id INT NOT NULL, business_name VARCHAR(255) NOT NULL, contact_first_name VARCHAR(255) NOT NULL, contact_last_name VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, comments VARCHAR(255) DEFAULT NULL, modified DATETIME NOT NULL, created DATE NOT NULL, INDEX IDX_7B6C42358DB60186 (task_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE forms (id INT AUTO_INCREMENT NOT NULL, task_type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, modified DATETIME NOT NULL, created DATE NOT NULL, INDEX IDX_FD3F1BF7DAADA679 (task_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, task_type_id INT DEFAULT NULL, form_id INT DEFAULT NULL, task_date DATE NOT NULL, visit_date DATETIME DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, status INT DEFAULT NULL, modified DATETIME NOT NULL, created DATE NOT NULL, INDEX IDX_50586597A76ED395 (user_id), INDEX IDX_50586597DAADA679 (task_type_id), INDEX IDX_505865975FF69B7D (form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE form_visit_data ADD CONSTRAINT FK_7B6C42358DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id)');
        $this->addSql('ALTER TABLE forms ADD CONSTRAINT FK_FD3F1BF7DAADA679 FOREIGN KEY (task_type_id) REFERENCES task_types (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597DAADA679 FOREIGN KEY (task_type_id) REFERENCES task_types (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_505865975FF69B7D FOREIGN KEY (form_id) REFERENCES forms (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE form_visit_data DROP FOREIGN KEY FK_7B6C42358DB60186');
        $this->addSql('ALTER TABLE forms DROP FOREIGN KEY FK_FD3F1BF7DAADA679');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597A76ED395');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597DAADA679');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_505865975FF69B7D');
        $this->addSql('DROP TABLE form_visit_data');
        $this->addSql('DROP TABLE forms');
        $this->addSql('DROP TABLE tasks');
    }
}
