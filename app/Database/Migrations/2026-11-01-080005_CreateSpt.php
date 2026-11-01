<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSpt extends Migration
{
    public function up(): void
{
    $this->forge->addField([
        'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'nomor'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        'tanggal_surat'     => ['type' => 'DATE', 'null' => true],
        'kegiatan'          => ['type' => 'TEXT'],
        'tempat'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        'tanggal_mulai'     => ['type' => 'DATE'],
        'tanggal_selesai'   => ['type' => 'DATE', 'null' => true],
        'mode_penerima'     => ['type' => 'ENUM', 'constraint' => ['tunggal', 'banyak'], 'default' => 'tunggal'],
        'instansi_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'pejabat_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'pembuat_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'status'            => ['type' => 'ENUM', 'constraint' => ['draft', 'diajukan', 'disetujui', 'ditolak'], 'default' => 'draft'],
        'catatan_revisi'    => ['type' => 'TEXT', 'null' => true],
        'nota_nomor'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        'nota_dari'         => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
        'nota_hal'          => ['type' => 'VARCHAR', 'constraint' => 150, 'default' => 'Penyampaian Konsep Surat Tugas'],
        'nota_pengirim_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        'created_at'        => ['type' => 'DATETIME', 'null' => true],
        'updated_at'        => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('instansi_id', 'instansi', 'id', 'CASCADE', 'RESTRICT');
    $this->forge->addForeignKey('pejabat_id', 'pejabat', 'id', 'CASCADE', 'RESTRICT');
    $this->forge->addForeignKey('pembuat_id', 'users', 'id', 'CASCADE', 'RESTRICT');
    $this->forge->addForeignKey('nota_pengirim_id', 'pegawai', 'id', 'CASCADE', 'SET NULL');
    $this->forge->createTable('spt');
}

public function down(): void
{
    $this->forge->dropTable('spt');
}
}
