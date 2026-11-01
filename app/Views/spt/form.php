<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $errors = session()->getFlashdata('errors') ?? [];
    $v      = static fn (string $f, $default = '') => old($f, $spt[$f] ?? $default, false);
    $json   = static fn ($x) => json_encode($x, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

    $dasarAwal     = array_values((array) old('dasar', $spt['dasar'] ?? [''], false));
    $untukAwal     = array_values((array) old('untuk', $spt['untuk'] ?? $untukDefault, false));
    $diperiksaAwal = array_values((array) old('diperiksa', $spt['diperiksa_ids'] ?? [], false));
    $terpilih      = array_map('strval', (array) old('pegawai_ids', $spt['pegawai_ids'] ?? [], false));
    $mode          = $v('mode_penerima', 'tunggal');
    $dikonsep      = (string) old('dikonsep_id', $spt['dikonsep_id'] ?? session('pegawai_id') ?? '', false);

    $opsiPegawai = array_map(static fn ($p) => ['id' => $p['id'], 'label' => $p['nama'] . ' — ' . $p['jabatan']], $pegawai);
        $pejabatTerpilih = (string) $v('pejabat_id');
    $instansiAwal    = '';
    foreach ($pejabat as $pj) {
        if ((string) $pj['id'] === $pejabatTerpilih) {
            $instansiAwal = (string) $pj['instansi_id'];
            break;
        }
    }
    if ($instansiAwal === '' && $instansi) {
        $instansiAwal = (string) $instansi[0]['id'];
    }

    $dataPejabat = array_map(static fn ($pj) => [
        'id' => $pj['id'], 'instansi_id' => $pj['instansi_id'], 'label' => $pj['nama'] . ' — ' . $pj['jabatan'],
    ], $pejabat);

    $infoKop = [];
    foreach ($instansi as $ins) {
        $infoKop[$ins['id']] = ! empty($ins['kop_file']);
    }
?>
<h3 class="mb-3"><?= esc($title) ?></h3>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <strong>Periksa kembali isian berikut:</strong>
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $action ?>" method="post">
    <?= csrf_field() ?>

    <!-- 1. Kegiatan -->
    <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold">1. Informasi Kegiatan</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Kegiatan <span class="text-danger">*</span></label>
                <textarea name="kegiatan" rows="3" class="form-control"
                            placeholder="Menghadiri Rapat Pembahasan Laporan Pendahuluan Kajian Penanganan Longsoran antara Stasiun Rantauprapat Baru – Pondok S5"><?= esc($v('kegiatan')) ?></textarea>
                <div class="form-text">Awali dengan kata kerja (Menghadiri, Mengikuti, Melaksanakan, ...), tanpa tanggal dan tempat. Tanggal dan tempat ditambahkan otomatis di judul Lampiran dan Nota Dinas.</div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="<?= esc($v('tanggal_mulai')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" value="<?= esc($v('tanggal_selesai')) ?>">
                    <div class="form-text">Kosongkan bila kegiatan 1 hari.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal surat</label>
                    <input type="date" name="tanggal_surat" class="form-control" value="<?= esc($v('tanggal_surat')) ?>">
                    <div class="form-text">Boleh kosong, diisi saat disetujui.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Tempat</label>
                    <input type="text" name="tempat" class="form-control" value="<?= esc($v('tempat')) ?>"
                           placeholder="Contoh: Hotel Grand Impression, Jl. Setia Budi, Medan">
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Dasar -->
    <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold">2. Dasar <span class="text-danger">*</span></div>
        <div class="card-body">
            <div id="wadah-dasar"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tambahTeks('wadah-dasar', 'dasar')">+ Tambah butir dasar</button>
        </div>
    </div>

    <!-- 3. Untuk -->
    <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold">3. Untuk (butir tugas) <span class="text-danger">*</span></div>
        <div class="card-body">
            <p class="text-muted small mb-2">Butir 1 biasanya kegiatannya (mis. "Menghadiri Rapat ... pada hari ... di ..."). Butir lain sudah terisi standar dan bisa diubah atau dihapus.</p>
            <div id="wadah-untuk"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tambahTeks('wadah-untuk', 'untuk')">+ Tambah butir tugas</button>
        </div>
    </div>

    <!-- 4. Penerima -->
    <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold">4. Pegawai yang Ditugaskan <span class="text-danger">*</span></div>
        <div class="card-body">
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="mode_penerima" id="m-tunggal" value="tunggal" <?= $mode === 'tunggal' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="m-tunggal">Satu pegawai (data ditulis langsung di surat)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="mode_penerima" id="m-banyak" value="banyak" <?= $mode === 'banyak' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="m-banyak">Banyak pegawai (daftar di Lampiran)</label>
                </div>
            </div>

            <input type="text" id="cari-penerima" class="form-control mb-2" placeholder="Cari nama atau jabatan...">
            <div class="border rounded p-2" style="max-height:260px; overflow:auto;">
                <?php foreach ($pegawai as $p):
                    $dipilih = in_array((string) $p['id'], $terpilih, true);
                    if (! $p['is_active'] && ! $dipilih) {
                        continue;
                    } ?>
                    <div class="form-check item-penerima">
                        <input class="form-check-input cb-penerima" type="checkbox" name="pegawai_ids[]"
                               id="pg<?= $p['id'] ?>" value="<?= $p['id'] ?>" <?= $dipilih ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pg<?= $p['id'] ?>">
                            <?= esc($p['nama']) ?> <small class="text-muted">— <?= esc($p['jabatan']) ?></small>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 5. Instansi dan Penandatangan -->
    <div class="card shadow-sm mb-3">
        <div class="card-header fw-semibold">5. Instansi dan Penandatangan <span class="text-danger">*</span></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Instansi</label>
                    <select id="instansi" class="form-select">
                        <?php foreach ($instansi as $ins): ?>
                            <option value="<?= $ins['id'] ?>" <?= $instansiAwal === (string) $ins['id'] ? 'selected' : '' ?>>
                                <?= esc($ins['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="info-kop" class="form-text"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Penandatangan</label>
                    <select name="pejabat_id" id="pejabat" class="form-select"></select>
                    <div id="info-pejabat" class="form-text text-danger"></div>
                </div>
            </div>
            <div class="form-text mt-2">Instansi menentukan kop surat, footer, kota, dan nama instansi pada surat.</div>
        </div>
    </div>

    <!-- 6. Paraf dan Nota Dinas -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">6. Paraf dan Nota Dinas</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Dikonsep oleh</label>
                <select name="dikonsep_id" class="form-select">
                    <option value="">-- Tidak diisi --</option>
                    <?php foreach ($pegawai as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $dikonsep === (string) $p['id'] ? 'selected' : '' ?>>
                            <?= esc($p['nama'] . ' — ' . $p['jabatan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label class="form-label">Diperiksa oleh</label>
            <div id="wadah-diperiksa"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-4" onclick="tambahDiperiksa()">+ Tambah pemeriksa</button>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nota Dinas: dari (jabatan pengirim)</label>
                    <input type="text" name="nota_dari" class="form-control" value="<?= esc($v('nota_dari')) ?>" placeholder="Kepala Seksi Prasarana">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nota Dinas: yang menandatangani</label>
                    <select name="nota_pengirim_id" class="form-select">
                        <option value="">-- Tidak diisi --</option>
                        <?php foreach ($pegawai as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (string) $v('nota_pengirim_id') === (string) $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['nama'] . ' — ' . $p['jabatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Nota Dinas: surat rujukan (opsional)</label>
                    <textarea name="nota_rujukan" rows="2" class="form-control"
                                placeholder="Surat Direktur Prasarana Perkeretaapian Nomor: UM.207/18/16/K3/DJKA/2023 tanggal 25 September 2023 perihal Undangan Rapat"><?= esc($v('nota_rujukan')) ?></textarea>
                    <div class="form-text">Jika diisi, kalimat Nota Dinas diawali "Menunjuk ...". Tulis tanpa tanda baca di akhir.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Nota Dinas: hal</label>
                    <input type="text" name="nota_hal" class="form-control" value="<?= esc($v('nota_hal', 'Penyampaian Konsep Surat Tugas')) ?>">
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary">Simpan sebagai Draft</button>
    <a href="<?= site_url('spt') ?>" class="btn btn-link">Batal</a>
</form>

<script>
const awalDasar     = <?= $json($dasarAwal) ?>;
const awalUntuk     = <?= $json($untukAwal) ?>;
const awalDiperiksa = <?= $json($diperiksaAwal) ?>;
const opsiPegawai   = <?= $json($opsiPegawai) ?>;

// Baris teks dinamis (Dasar dan Untuk)
function tambahTeks(wadah, nama, nilai = '') {
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    const ta = document.createElement('textarea');
    ta.name = nama + '[]';
    ta.rows = 2;
    ta.className = 'form-control';
    ta.value = nilai;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-danger';
    btn.textContent = '×';
    btn.onclick = () => div.remove();
    div.append(ta, btn);
    document.getElementById(wadah).appendChild(div);
}

// Baris pemeriksa (dropdown pegawai)
function tambahDiperiksa(nilai = '') {
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    const sel = document.createElement('select');
    sel.name = 'diperiksa[]';
    sel.className = 'form-select';
    const kosong = document.createElement('option');
    kosong.value = '';
    kosong.textContent = '-- Pilih pegawai --';
    sel.appendChild(kosong);
    opsiPegawai.forEach(o => {
        const op = document.createElement('option');
        op.value = o.id;
        op.textContent = o.label;
        if (String(o.id) === String(nilai)) op.selected = true;
        sel.appendChild(op);
    });
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-danger';
    btn.textContent = '×';
    btn.onclick = () => div.remove();
    div.append(sel, btn);
    document.getElementById('wadah-diperiksa').appendChild(div);
}

awalDasar.forEach(t => tambahTeks('wadah-dasar', 'dasar', t));
awalUntuk.forEach(t => tambahTeks('wadah-untuk', 'untuk', t));
awalDiperiksa.forEach(id => tambahDiperiksa(id));

// Pencarian di daftar penerima
document.getElementById('cari-penerima').addEventListener('input', e => {
    const kata = e.target.value.toLowerCase();
    document.querySelectorAll('.item-penerima').forEach(el => {
        el.classList.toggle('d-none', !el.textContent.toLowerCase().includes(kata));
    });
});

// Mode "satu pegawai": hanya boleh satu centang
const cbs = document.querySelectorAll('.cb-penerima');
const modeSekarang = () => document.querySelector('input[name="mode_penerima"]:checked').value;
cbs.forEach(cb => cb.addEventListener('change', () => {
    if (cb.checked && modeSekarang() === 'tunggal') cbs.forEach(o => { if (o !== cb) o.checked = false; });
}));
document.querySelectorAll('input[name="mode_penerima"]').forEach(r => r.addEventListener('change', () => {
    if (modeSekarang() !== 'tunggal') return;
    let sudah = false;
    cbs.forEach(o => { if (o.checked) { if (sudah) o.checked = false; sudah = true; } });
}));

// Instansi memfilter daftar penandatangan dan menampilkan status kop
const dataPejabat  = <?= $json($dataPejabat) ?>;
const infoKop      = <?= $json($infoKop) ?>;
const pejabatAwal  = <?= $json($pejabatTerpilih) ?>;
const selInstansi  = document.getElementById('instansi');
const selPejabat   = document.getElementById('pejabat');

function isiPejabat(instansiId, pilih = '') {
    selPejabat.innerHTML = '';
    const kosong = document.createElement('option');
    kosong.value = '';
    kosong.textContent = '-- Pilih pejabat --';
    selPejabat.appendChild(kosong);

    const daftar = dataPejabat.filter(p => String(p.instansi_id) === String(instansiId));
    daftar.forEach(p => {
        const op = document.createElement('option');
        op.value = p.id;
        op.textContent = p.label;
        if (String(p.id) === String(pilih)) op.selected = true;
        selPejabat.appendChild(op);
    });
    if (!pilih && daftar.length === 1) selPejabat.value = daftar[0].id; // hanya satu: pilih otomatis

    document.getElementById('info-pejabat').textContent = daftar.length
        ? '' : 'Instansi ini belum punya pejabat aktif. Minta super user menambahkannya di menu Pejabat.';
    document.getElementById('info-kop').textContent = infoKop[instansiId]
        ? 'Kop surat: sudah diunggah, otomatis dipakai saat cetak.'
        : 'Kop surat: belum diunggah, PDF akan tercetak tanpa kop.';
}

selInstansi.addEventListener('change', () => isiPejabat(selInstansi.value));
isiPejabat(selInstansi.value, pejabatAwal);
</script>
<?= $this->endSection() ?>