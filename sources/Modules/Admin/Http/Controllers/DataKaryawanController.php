<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\MiddlewareController;
use App\Models\DataDosenTendik;
use App\Services\TsuErrorHandlerService;
use Illuminate\Http\Request;
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
                // LOGIC JABATAN STRUKTURAL
                $strukturalRaw = $row->jabatan_struktural ?? '';
                $jabatansStr = array_filter(array_map('trim', explode(',', $strukturalRaw)));
                
                if (empty($jabatansStr)) {
                    $htmlStruktural = '
                        <div class="d-flex align-items-center mb-1 text-muted">
                            <div class="bg-light rounded-circle mr-2 d-flex justify-content-center align-items-center border shadow-sm" style="width: 24px; height: 24px; min-width: 24px;">
                                <i class="fas fa-minus text-muted" style="font-size: 0.65rem;"></i>
                            </div>
                            <span class="font-italic" style="font-size: 0.85rem;">Tidak ada struktural</span>
                        </div>';
                } else {
                    $htmlStruktural = '';
                    foreach($jabatansStr as $jab) {
                        $htmlStruktural .= '
                        <div class="d-flex align-items-center mb-1">
                            <div class="bg-dark rounded-circle mr-2 d-flex justify-content-center align-items-center shadow-sm" style="width: 24px; height: 24px; min-width: 24px;">
                                <i class="fas fa-briefcase text-white" style="font-size: 0.65rem;"></i>
                            </div>
                            <span class="font-weight-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">'.htmlspecialchars($jab).'</span>
                        </div>';
                    }
                }

                // LOGIC JABATAN FUNGSIONAL
                $fungsionalRaw = $row->jabatan_fungsional ?? '';
                $jabatansFung = array_filter(array_map('trim', explode(',', $fungsionalRaw)));
                
                if (empty($jabatansFung)) {
                    $htmlFungsional = '
                        <div class="d-flex align-items-center mt-2 text-muted">
                            <div class="bg-light rounded-circle mr-2 d-flex justify-content-center align-items-center border shadow-sm" style="width: 24px; height: 24px; min-width: 24px;">
                                <i class="fas fa-minus text-muted" style="font-size: 0.65rem;"></i>
                            </div>
                            <span class="font-italic" style="font-size: 0.85rem;">Tidak ada fungsional</span>
                        </div>';
                } else {
                    $htmlFungsional = '';
                    foreach($jabatansFung as $jabFung) {
                        $htmlFungsional .= '
                        <div class="d-flex align-items-center mt-2">
                            <div class="bg-info rounded-circle mr-2 d-flex justify-content-center align-items-center shadow-sm" style="width: 24px; height: 24px; min-width: 24px;">
                                <i class="fas fa-medal text-white" style="font-size: 0.65rem;"></i>
                            </div>
                            <span class="font-weight-bold text-info" style="font-size: 0.85rem; line-height: 1.2;">'.htmlspecialchars($jabFung).'</span>
                        </div>';
                    }
                }

                return $htmlStruktural . $htmlFungsional;
            })
            ->addColumn('status_karyawan', function($row) {
                $statusHtml = '';
                if ($row->status_karyawan) {
                    $statusHtml = '<span class="badge ' . $row->status_karyawan->color() . ' mb-1">' . strtoupper($row->status_karyawan->label()) . '</span><br>';
                } else {
                    $statusHtml = '<span class="badge badge-secondary mb-1">BELUM DISET</span><br>';
                }

                if ($row->is_active == 1) {
                    $aktifHtml = '<span class="badge badge-success px-2"><i class="fas fa-check-circle"></i> AKTIF</span>';
                } else {
                    $aktifHtml = '<span class="badge badge-danger px-2"><i class="fas fa-times-circle"></i> NON-AKTIF</span>';
                }

                return $statusHtml . $aktifHtml;
            })
            ->addColumn('aksi', function ($row) {
                $showUrl = route('admin.data-karyawan.show', $row->id);

                $btnDetail = '<button type="button" class="btn btn-sm btn-info text-white mx-1 btn-modal" data-url="'.$showUrl.'" title="Detail Profil"><i class="fas fa-eye"></i></button>';

                return '<div class="d-flex justify-content-center align-items-center">' . $btnDetail . '</div>';
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
}
