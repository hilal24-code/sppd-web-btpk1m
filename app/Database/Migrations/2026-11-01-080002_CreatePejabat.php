<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePejabat extends Migration
{
    public function up(): void
{
    $this->forge->addField([
        'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'instansi_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'nama'        => ['type' => 'VARCHAR', 'constraint' => 150],
        'nip'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        'jabatan'     => ['type' => 'VARCHAR', 'constraint' => 150],
        'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        'created_at'  => ['type' => 'DATETIME', 'null' => true],
        'updated_at'  => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('instansi_id', 'instansi', 'id', 'CASCADE', 'CASCADE');
    $this->forge->createTable('pejabat');
}

public function down(): void
{
    $this->forge->dropTable('pejabat');
}
}
