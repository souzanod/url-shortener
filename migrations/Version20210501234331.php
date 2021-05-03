<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20210501234331 extends AbstractMigration
{
    private $tableName = "urls";
    
    public function getDescription(): string
    {
        return 'create urls table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable($this->tableName);

        $table->addColumn(
            'id',
            Types::INTEGER,
            ['autoincrement' => true]
        );
        $table->setPrimaryKey(['id']);

        $table->addColumn('hash', Types::STRING, ['nullable' => false]);
        $table->addUniqueIndex(["hash"], "uniq_hash");

        $table->addColumn('url', Types::STRING, ['nullable' => false]);

        $table->addColumn('expiresAt', Types::DATETIME_MUTABLE, ['nullable' => false]);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable($this->tableName);
    }
}
