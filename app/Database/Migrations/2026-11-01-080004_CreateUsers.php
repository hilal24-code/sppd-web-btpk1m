<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up(): void
{
    $this->forge->addField([
        'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'pegawai_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        'username'   => ['type' => 'VARCHAR', 'constraint' => 50],
        'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
        'role'       => ['type' => 'ENUM', 'constraint' => ['user', 'admin', 'superuser'], 'default' => 'user'],
        'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        'created_at' => ['type' => 'DATETIME', 'null' => true],
        'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addUniqueKey('username');
    $this->forge->addForeignKey('pegawai_id', 'pegawai', 'id', 'CASCADE', 'SET NULL');
    $this->forge->createTable('users');
}

public function down(): void
{
    $this->forge->dropTable('users');
}
}
