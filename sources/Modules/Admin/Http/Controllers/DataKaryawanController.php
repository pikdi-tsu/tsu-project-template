<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DataKaryawanController extends MiddlewareController
{
    public function __construct()
    {
        $this->registerPermissions('admin:data-karyawan');
    }

    /**
     * Menampilkan halaman utama Data Karyawan
     */
    public function index()
    {
        return view('admin::data-karyawan.index', ['title' => 'Data Dosen & Tendik']);
    }

    /**
     * Sumber data JSON untuk Yajra DataTables
     */
    public function datatable()
    {
        // Tarik data mentah
        $data = DataDosenTendik::query()->orderBy('nama', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nama_lengkap', function($row) {
                // Nama beserta gelar
                $gelarDepan = $row->gelar_depan ? $row->gelar_depan . ' ' : '';
                $gelarBelakang = $row->gelar_belakang ? ', ' . $row->gelar_belakang : '';
                $namaLengkap = $gelarDepan . $row->nama . $gelarBelakang;

                // No.HP
                $kontak = '';
                if (!empty($row->no_hp)) {
                    $href = str_starts_with($row->no_hp, 'wa.me/') ? 'https://' . $row->no_hp : $row->no_hp;
                    $teksTampil = str_replace('wa.me/', '', $row->no_hp);

                    $kontak = '<div class="mt-2">
                       <a href="' . $href . '" target="_blank" class="text-success font-weight-bold shadow-sm"
                          style="background-color: #e8f5e9; padding: 4px 10px; border-radius: 20px; text-decoration: none; font-size: 0.75rem; border: 1px solid #c8e6c9; display: inline-block;">
                           <i class="fab fa-whatsapp mr-1" style="font-size: 0.9rem;"></i> Chat WA
                       </a>
                   </div>';
                }

                return '<div class="font-weight-bold text-dark text-uppercase" style="line-height: 1.2;">' . $namaLengkap . '</div>' . $kontak;
            })
            ->addColumn('identitas', function($row) {
                // Badge NIK & NIDN
                $nik = '<span class="badge badge-secondary mb-1">NIK: ' . $row->nik . '</span><br>';
                $warnaNidn = $row->nidn ? 'badge-success' : 'badge-warning';
                $teksNidn = $row->nidn ?? 'Tidak Ada';
                $nidn = '<span class="badge ' . $warnaNidn . '">NIDN: ' . $teksNidn . '</span>';

                return $nik . $nidn;
            })
            ->addColumn('jabatan', function($row) {
                // Jabatan Struktural & Fungsional
                $struktural = $row->jabatan_struktural ?? 'Tidak menjabat struktural';
                $fungsional = $row->jabatan_fungsional ?? '-';

                return '<div class="font-weight-bold text-dark">' . $struktural . '</div>' .
                    '<div class="text-muted small">Fungsional: ' . $fungsional . '</div>';
            })
            ->addColumn('status_karyawan', function($row) {
                if ($row->status_karyawan === 'NON-AKTIF') {
                    return '<span class="badge badge-danger">' . $row->status_karyawan . '</span>';
                }
                return '<span class="badge badge-success">' . ($row->status_karyawan) . '</span>';
            })
            ->addColumn('aksi', function ($row) {
                $showUrl = route('admin.data-karyawan.show', $row->id);
                $editUrl = route('admin.data-karyawan.edit', $row->id);
                $deleteUrl = route('admin.data-karyawan.destroy', $row->id);
                $token = csrf_token();

                $btnDetail = '<button type="button" class="btn btn-sm btn-info text-white mx-1 btn-modal" data-url="'.$showUrl.'" title="Detail Profil"><i class="fas fa-eye"></i></button>';
                $btnEdit = '<button type="button" class="btn btn-sm btn-warning btn-edit text-dark mx-1" data-url="'.$editUrl.'" title="Edit Profil"><i class="fas fa-pencil-alt"></i></button>';

                if ($row->status_karyawan === 'NON-AKTIF') {
                    // JIKA NON-AKTIF: Tombol HIJAU (Nembak ke route POST 'aktifkan')
                    $aktifkanUrl = route('admin.data-karyawan.bio-aktif', $row->id);
                    $btnToggle = '
                        <form action="'.$aktifkanUrl.'" method="POST" style="display:inline-block; margin: 0;" class="mx-1">
                            <input type="hidden" name="_token" value="'.$token.'">
                            <button type="button" class="btn btn-sm btn-success btn-toggle-status" data-action="aktifkan" data-name="'. htmlspecialchars($row->nama) .'" title="Aktifkan Karyawan">
                                <i class="fas fa-user-check"></i>
                            </button>
                        </form>
                    ';
                } else {
                    // JIKA AKTIF: Tombol MERAH (Nembak ke route DELETE 'destroy')
                    $deleteUrl = route('admin.data-karyawan.destroy', $row->id);
                    $btnToggle = '
                        <form action="'.$deleteUrl.'" method="POST" style="display:inline-block; margin: 0;" class="mx-1">
                            <input type="hidden" name="_token" value="'.$token.'">
                            <input type="hidden" name="_method" value="DELETE"> <button type="button" class="btn btn-sm btn-danger btn-toggle-status" data-action="nonaktifkan" data-name="'. htmlspecialchars($row->nama) .'" title="Nonaktifkan Karyawan">
                                <i class="fas fa-user-slash"></i>
                            </button>
                        </form>
                    ';
                }

                return '<div class="d-flex justify-content-center align-items-center">' . $btnDetail . $btnEdit . $btnToggle . '</div>';
            })
            ->rawColumns(['nama_lengkap', 'identitas', 'jabatan', 'status_karyawan', 'aksi'])
            ->make(true);
    }

    /**
     * Menampilkan Modal Detail Karyawan (Read Only)
     */
    public function show($id)
    {
        $karyawan = DataDosenTendik::findOrFail($id);
        $formConfig = DataDosenTendik::getFormConfig();

        return view('admin::data-karyawan.show_modal', compact('karyawan', 'formConfig'));
    }

    /**
     * Menampilkan Modal Form Tambah
     */
    public function create()
    {
        $this->guard('create', 'admin:data-karyawan');

        $formConfig = DataDosenTendik::getFormConfig();

        // Pastikan file view ini nanti dibuat ya Bosku
        return view('admin::data-karyawan.create_modal', compact('formConfig'));
    }

    /**
     * Menyimpan data dari Modal Form
     */
    public function store(Request $request)
    {
        $this->guardStore($request->id, 'admin:data-karyawan');

        // Validasi sesuaikan dengan kebutuhan field-mu
        $request->validate([
            'nik'  => 'required|unique:data_dosen_tendiks,nik',
            'nama' => 'required|string|max:255',
        ]);

        try {
            $data = $request->all();

            if (!empty($data['no_hp'])) {
                $cleanPhone = str_replace(['+', ' '], '', trim($data['no_hp']));

                if (!str_starts_with($cleanPhone, 'wa.me/')) {
                    $data['no_hp'] = 'wa.me/' . $cleanPhone;
                }
            }

            DataDosenTendik::create($request->all());

            return back()->with('success', 'Data karyawan berhasil ditambahkan!');
        } catch (\Exception $e) {
            // Error Handling ala PIKDI TSU
            $rawMessage = $e->getMessage();
            $errorCode  = "[TSU_KARYAWAN_STORE_FAIL]";
            $userMsg    = "Gagal menyimpan data karyawan baru.";

            if (preg_match('/\[TSU_.*?\]/', $rawMessage, $matches)) {
                $errorCode = $matches[0];
                $userMsg = trim(str_replace($errorCode, '', $rawMessage));
            }

            Log::error("$errorCode Gagal Create Karyawan.", [
                'original_error' => $rawMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $finalErrorMsg = "<div class='text-center'>
                                <h4 class='text-bold text-danger mb-2'>$errorCode</h4>
                                <p class='mb-2 text-bold' style='font-size: 1.1em;'>$userMsg</p>
                                <p class='text-muted small mb-0'>Silakan screenshot pesan ini dan laporkan ke PIKDI jika masalah berlanjut.</p>
                              </div>";

            return back()->withInput($request->all())->with('error', $finalErrorMsg);
        }
    }

    /**
     * Menampilkan Modal Form Edit
     */
    public function edit($id)
    {
        $this->guard('edit', 'admin:data-karyawan');

        $formConfig = DataDosenTendik::getFormConfig();
        $karyawan = DataDosenTendik::findOrFail($id);

        return view('admin::data-karyawan.edit_modal', compact('karyawan', 'formConfig'));
    }

    /**
     * Menyimpan update data dari Modal
     */
    public function update(Request $request, $id)
    {
        $this->guard('edit', 'admin:data-karyawan');

        $karyawan = DataDosenTendik::findOrFail($id);

        $request->validate([
            'nik'  => 'required|unique:data_dosen_tendiks,nik,' . $id,
            'nama' => 'required|string|max:255',
        ]);

        try {
            $data = $request->except(['id', 'user_id', 'status_karyawan']);

            if (!empty($data['no_hp'])) {
                $cleanPhone = str_replace(['+', ' '], '', trim($data['no_hp']));
                if (!str_starts_with($cleanPhone, 'wa.me/')) {
                    $data['no_hp'] = 'wa.me/' . $cleanPhone;
                }
            }

            $karyawan->update($data);

            return back()->with('success', 'Data karyawan berhasil diperbarui!');
        } catch (\Exception $e) {
            // Error Handling ala PIKDI TSU
            $rawMessage = $e->getMessage();
            $errorCode  = "[TSU_KARYAWAN_UPD_FAIL]";
            $userMsg    = "Gagal menyimpan perubahan data karyawan.";

            if (preg_match('/\[TSU_.*?\]/', $rawMessage, $matches)) {
                $errorCode = $matches[0];
                $userMsg = trim(str_replace($errorCode, '', $rawMessage));
            }

            Log::error("$errorCode Gagal Update Karyawan ID: $id.", [
                'original_error' => $rawMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            $finalErrorMsg = "<div class='text-center'>
                                <h4 class='text-bold text-danger mb-2'>$errorCode</h4>
                                <p class='mb-2 text-bold' style='font-size: 1.1em;'>$userMsg</p>
                                <p class='text-muted small mb-0'>Silakan screenshot pesan ini dan laporkan ke PIKDI jika masalah berlanjut.</p>
                              </div>";

            return back()->withInput($request->all())->with('error', $finalErrorMsg);
        }
    }

    /**
     * Menghapus Data Karyawan (Nonaktifkan)
     */
    public function destroy($id)
    {
        $this->guard('delete', 'admin:karyawan');
        $karyawan = DataDosenTendik::findOrFail($id);

        try {
            $karyawan->update([
                'user_id' => null,
                'status_karyawan' => 'NON-AKTIF'
            ]);
            return back()->with('success', 'Data karyawan berhasil dinonaktifkan.');
        } catch (\Exception $e) {
            Log::error("[TSU_KARYAWAN_DEL_FAIL] Gagal Nonaktifkan ID: $id. Error: " . $e->getMessage());
            return back()->with('error', 'Gagal menonaktifkan data karyawan.');
        }
    }

    /**
     * Mengaktifkan Kembali Data Karyawan
     */
    public function bioAktif($id)
    {
        $this->guard('edit', 'admin:data-karyawan');
        $karyawan = DataDosenTendik::findOrFail($id);

        try {
            $karyawan->update([
                'status_karyawan' => 'AKTIF'
            ]);
            return back()->with('success', 'Mantap! Data karyawan berhasil diaktifkan kembali.');
        } catch (\Exception $e) {
            Log::error("[TSU_KARYAWAN_ACT_FAIL] Gagal Aktifkan ID: $id. Error: " . $e->getMessage());
            return back()->with('error', 'Gagal mengaktifkan kembali data karyawan.');
        }
    }
}
