<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSptParaf extends Migration
{
public function up(): void
{
    $this->forge->addField([
        'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'spt_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'uraian'     => ['type' => 'ENUM', 'constraint' => ['Dikonsep', 'Diperiksa']],
        'pegawai_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'urutan'     => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 1],
        'tanggal'    => ['type' => 'DATE', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
    $this->forge->addForeignKey('pegawai_id', 'pegawai', 'id', 'CASCADE', 'RESTRICT');
    $this->forge->createTable('spt_paraf');
}

public function down(): void
{
    $this->forge->dropTable('spt_paraf');
}
}
