<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKopDanRujukan extends Migration
{
public function up(): void
{
    $this->forge->addColumn('instansi', [
        'kop_file'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'kota'],
        'footer_file' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'kop_file'],
    ]);

    $this->forge->addColumn('spt', [
        'nota_rujukan' => ['type' => 'TEXT', 'null' => true, 'after' => 'nota_pengirim_id'],
    ]);
}

public function down(): void
{
    $this->forge->dropColumn('instansi', ['kop_file', 'footer_file']);
    $this->forge->dropColumn('spt', 'nota_rujukan');
}
}
