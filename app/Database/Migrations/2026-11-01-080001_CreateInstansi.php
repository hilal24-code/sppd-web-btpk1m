<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInstansi extends Migration
{
public function up(): void
{
    $this->forge->addField([
        'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'nama'       => ['type' => 'VARCHAR', 'constraint' => 150],
        'kota'       => ['type' => 'VARCHAR', 'constraint' => 80, 'default' => 'Medan'],
        'created_at' => ['type' => 'DATETIME', 'null' => true],
        'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->createTable('instansi');
}

public function down(): void
{
    $this->forge->dropTable('instansi');
}
}
