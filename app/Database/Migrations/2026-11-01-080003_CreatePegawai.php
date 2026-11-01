<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePegawai extends Migration
{
    public function up(): void
{
    $this->forge->addField([
        'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'nama'             => ['type' => 'VARCHAR', 'constraint' => 150],
        'nip'              => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        'pangkat_golongan' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
        'jabatan'          => ['type' => 'VARCHAR', 'constraint' => 150],
        'seksi'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        'is_active'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        'created_at'       => ['type' => 'DATETIME', 'null' => true],
        'updated_at'       => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->createTable('pegawai');
}

public function down(): void
{
    $this->forge->dropTable('pegawai');
}
}
