<?php

declare(strict_types=1);

namespace ForumifyDoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240121132454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change settings to JSON';
    }

    public function up(Schema $schema): void
    {
        $settings = $this->connection->fetchAllAssociative('SELECT * FROM setting');
        $this->connection->executeStatement('TRUNCATE TABLE setting');

        $this->addSql('ALTER TABLE setting CHANGE value value JSON NOT NULL');

        foreach ($settings as $setting) {
            $value = is_numeric($setting['value'])
                ? (float)$setting['value']
                : $setting['value'];

            $this->addSql('INSERT INTO setting VALUES (?, ?)', [$setting['key'], json_encode($value)]);
        }
    }

    public function down(Schema $schema): void
    {
        $settings = $this->connection->fetchAllAssociative('SELECT * FROM setting');
        $this->connection->executeStatement('TRUNCATE TABLE setting');

        $this->addSql('ALTER TABLE setting CHANGE value value LONGTEXT NOT NULL');

        foreach ($settings as $setting) {
            $this->addSql('INSERT INTO setting VALUES (?, ?)', [$setting['key'], (string)json_decode($setting['value'])]);
        }
    }
}
