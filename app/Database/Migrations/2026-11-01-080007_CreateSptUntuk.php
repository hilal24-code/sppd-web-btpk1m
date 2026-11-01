<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSptUntuk extends Migration
{
public function up(): void
{
    $this->forge->addField([
        'id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'spt_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'urutan' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 1],
        'isi'    => ['type' => 'TEXT'],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
    $this->forge->createTable('spt_untuk'); // di file SptUntuk: 'spt_untuk'
}

public function down(): void
{
    $this->forge->dropTable('spt_untuk'); // di file SptUntuk: 'spt_untuk'
}
}
