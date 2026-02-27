<?php

namespace Modules\System\Http\Controllers;

use App\Models\GroupUserModel;
use App\Models\MahasiswaModel;
use App\Models\MasterGroupModel;
use App\Models\ModulModel;
use App\Models\PegawaiModel;
use App\Models\User;
use App\Models\UserResetPasswordModel;
use App\User as AppUser;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\DataTables;

use Session, Crypt, DB;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('checklogin');
    }
    //change password normal
    public function showChangePassword()
    {
        $data = array(
            'title' => 'Change Password',
            'menu'  => 'Change Password',
        );
        return view('system::setting/changepassword', $data);
    }

    public function saveChangePassword(Request $post)
    {
        // header("Access-Control-Allow-Origin: *");
        // header("Access-Control-Allow-Headers: *");
        // dd($post->_token);
        //cek password
        $oldpass = $post->oldpass;
        $newpass = $post->newpass;
        $newpass2 = $post->newpass2;
        // dd(preg_match('/\d/', $$oldpass));
        if((preg_match('/[[:punct:]]/', $oldpass))==1||(preg_match('/[A-Z]/', $oldpass))==0||(preg_match('/\d/', $oldpass))==0||strlen($oldpass)<8){
            Session::flash('alert', ['title' => 'Gagal','message' => 'Pergantian Password Gagal ! Silahkan Baca Note !','status' => 'error']);
            return redirect()->back();
        }

        if((preg_match('/[[:punct:]]/', $newpass))==1||(preg_match('/[A-Z]/', $newpass))==0||(preg_match('/\d/', $newpass))==0||strlen($newpass)<8){
            Session::flash('alert', ['title' => 'Gagal','message' => 'Pergantian Password Gagal ! Silahkan Baca Note !','status' => 'error']);
            return redirect()->back();
        }

        if((preg_match('/[[:punct:]]/', $newpass2))==1||(preg_match('/[A-Z]/', $newpass2))==0||(preg_match('/\d/', $newpass2))==0||strlen($newpass2)<8){
            Session::flash('alert', ['title' => 'Gagal','message' => 'Pergantian Password Gagal ! Silahkan Baca Note !','status' => 'error']);
            return redirect()->back();
        }

        if($newpass!=$newpass2){
            Session::flash('alert', ['title' => 'Gagal','message' => 'Password Baru Tidak Sama ! Silahkan Ulangi !','status' => 'error']);
            return redirect()->back();
        }

        $nik = session('session')['user_nik'];
        $email = session('session')['email'];

        $cek = User::where('nik',$nik)->where('email',$email)->where('isactive',1)->first();
        // dd(Hash::check($oldpass,$cek->password));
        if($cek){
            if(Hash::check($newpass,$cek->password)){
                Session::flash('alert', ['title' => 'Gagal','message' => 'Password Baru Tidak Boleh Sama Seperti Password Lama !','status' => 'error']);
                return redirect()->back();
            }
            if(Hash::check($oldpass,$cek->password)){
                $user = array(
                    'password' => Hash::make($newpass),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $nik
                );
                $up1 = User::where('nik',$nik)->where('email',$email)->where('isactive',1)->update($user);
                $logreset = array(
                    'email' => $email,
                    'token' => $post->_token,
                    'activity' => 'Change Password',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => session('session')['user_nik']
                );
                $up2 = UserResetPasswordModel::insert($logreset);
                if($up1 && $up2){
                    Session::flash('alert', ['title' => 'Berhasil','message' => 'Password Berubah','status' => 'success']);
                    return redirect()->back();
                }else{
                    Session::flash('alert', ['title' => 'Gagal','message' => 'Password Tidak Berubah !','status' => 'error']);
                    return redirect()->back();
                }
            }else{
                Session::flash('alert', ['title' => 'Gagal','message' => 'Password Salah !','status' => 'error']);
                return redirect()->back();
            }
        }else{
            Session::flash('alert', ['title' => 'Gagal','message' => 'User Tidak Ada ! Silahkan Hubungi Team IT','status' => 'error']);
            return redirect()->back();
        }
    }
    //END change password normal

    //Edit Profile
    public function showEditProfile()
    {
        // dd(session()->all());
        $data = array(
            'title' => 'Change Profile',
            'menu'  => 'Change Profile',
        );
        // dd(session('session')['user_nik']);
//        return view('system::setting/editprofile', $data);
        return route('profile', $data);
    }

    public function saveEditProfile(Request $post)
    {
        // dd($post);
        if ($post->hasFile('photoprofile')) {
            // $file->getClientOriginalName() -> mengambil nama file
            $file = $post->file('photoprofile');
            $ext = $file->getClientOriginalExtension();
            // dd($ext);
            $filename = time() . '_' . session('session')['user_nik'].'.'.$ext;
            $file->storeAs('FILE_PHOTOPROFILE', $filename);
            User::where('nik',session('session')['user_nik'])->where('email',session('session')['email'])->where('isactive',1)->update([
                'photo_profile' => $filename,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => session('session')['user_nik']
            ]);
            Session::flash('alert', ['title' => 'Berhasil','message' => 'Profil Berhasil Diperbarui','status' => 'success']);
            return redirect()->back();
        }else{
            Session::flash('alert', ['title' => 'Gagal','message' => 'Foto Tidak ditemukan','status' => 'error']);
            return redirect()->back();
        }
    }
    //END Edit Profile

    //User Management
    //Pegawai
    public function userManagement()
    {
        // dd(session()->all());
        $master = MasterGroupModel::where('isactive',1)->selectRaw('KodeGroupUser,NamaGroup')->get();
        $data = array(
            'title' => 'User Management',
            'menu'  => 'User Management',
            'mastergroup' => $master
        );

        return view('system::setting/usermanagement', $data);
    }

    public function table_pegawai()
    {
        $cek = MasterGroupModel::whereNotIn('NamaGroup',['MAHASISWA'])->select('KodeGroupUser')->get();
        $aa = array();

        foreach($cek as $q){
            $aa[] = $q->KodeGroupUser;
        }
        $data = User::whereIn('role_access',$aa)->where('isactive',1)->selectRaw('id,nik,email,role_access')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nip', function ($d) {
                return $d->nik;
            })
            ->addColumn('nama', function ($d) {
                $cek = PegawaiModel::where('nip',$d->nik)->where('email_kampus',$d->email)->select('nama')->first();
                $nama = $cek==null ? '-' : $cek->nama;
                return $nama;
            })
            ->addColumn('email', function ($d) {
                return $d->email;
            })
            ->addColumn('role', function ($d) {
                $cek = MasterGroupModel::where('KodeGroupUser',$d->role_access)->selectRaw('NamaGroup')->first();
                return $cek==null ? '-' : $cek->NamaGroup;
            })
            ->addColumn('action', function ($d) {
                $id = encrypt($d->id);
                $detail = '';
                $edit = '<a href="#" data-id="'.$id.'" class="btn_edit"><i title="Edit Data" class="fa fa-edit text-orange"></i></a>';
                $delete = '<a href="#" data-id="'.$id.'" class="btn_delete"><i title="Delete Data" class="fa fa-trash text-red"></i></a>';
                // $detail = '<a href="#" data-id="'.$id.'" class="btn_detail"><i title="Detail Content" class="fas fa-info-circle"></i></a>';

                return $detail . '  ' . $edit . '  ' . $delete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function table_mahasiswa()
    {
        $cek = MasterGroupModel::where('NamaGroup','MAHASISWA')->selectRaw('KodeGroupUser')->first();
        $data = User::where('role_access',$cek->KodeGroupUser)->where('isactive',1)->selectRaw('id,nik,email,role_access')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nim', function ($d) {
                return $d->nik;
            })
            ->addColumn('nama', function ($d) {
                $cek = MahasiswaModel::where('nim',$d->nik)->where('email',$d->email)->select('nama')->first();
                $nama = $cek==null ? '-' : $cek->nama;

                return $nama;
            })
            ->addColumn('email', function ($d) {
                return $d->email;
            })
            ->addColumn('role', function ($d) {
                $cek = MasterGroupModel::where('KodeGroupUser',$d->role_access)->selectRaw('NamaGroup')->first();
                return $cek==null ? '-' : $cek->NamaGroup;
            })
            ->addColumn('action', function ($d) {
                $id = encrypt($d->id);
                $detail = '';
                // $edit = '<a href="#" data-id="'.$id.'" class="btn_edit"><i title="Edit Data" class="fa fa-edit text-orange"></i></a>';
                $edit = '';
                $delete = '<a href="#" data-id="'.$id.'" class="btn_delete"><i title="Delete Data" class="fa fa-trash text-red"></i></a>';
                // $detail = '<a href="#" data-id="'.$id.'" class="btn_detail"><i title="Detail Content" class="fas fa-info-circle"></i></a>';

                return $detail . '  ' . $edit . '  ' . $delete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function searchNama(Request $post)
    {
        $query = $post->get('q');
        $role = $post->get('role');
        // dd($query,$role);

        $cek = MasterGroupModel::where('KodeGroupUser',$role)->selectRaw('NamaGroup')->first();

        if($cek->NamaGroup=='MAHASISWA'){
            $data = MahasiswaModel::where('nama', 'LIKE', "%{$query}%")
            ->orWhere('nim','LIKE',"%{$query}%")
            ->where('status_mahasiswa','aktif')
            ->whereNotNull('email')
            ->selectRaw('nim as nip, nama')
            ->limit(10)
            ->get();
        }else{
            $data = PegawaiModel::where('nama', 'LIKE', "%{$query}%")
            ->orWhere('nip','LIKE',"%{$query}%")
            ->whereNotNull('email_kampus')
            ->select('nip', 'nama')
            ->limit(10)
            ->get();
        }

        return response()->json($data);
    }

    public function StoreUser(Request $post)
    {
        // dd($post);
        if($post->userid==null){
            $alert = $this->SaveUser($post);
        }else{
            $alert = $this->EditSaveUser($post);
        }
        return redirect()->back()->with('alert',$alert);
    }

    public function SaveUser($post)
    {
        $role = $post->roleaccess;
        $nik = $post->nik;
        // dd($nik);
        if($role==null||$nik==null){
            return redirect()->back()->with('alert',['title' => 'Information','message' => 'User dan Role Akses tidak boleh kosong !','status' => 'warning']);
        }

        $cek = User::where('nik',$nik)->where('isactive',1)->first();
        if($cek){
            return redirect()->back()->with('alert',['title' => 'Information','message' => 'User Sudah Terdaftar','status' => 'warning']);
        }

        $cek1 = null;
        $email = null;
        $cek2 = MasterGroupModel::where('KodeGroupUser',$role)->selectRaw('NamaGroup')->first();
        if($cek2->NamaGroup=='MAHASISWA'){
            $cek1 = MahasiswaModel::where('nim',$nik)->select('nim','email')->first();
            $email = $cek1->email;
        }else{
            $cek1 = PegawaiModel::where('nip',$nik)->select('nip','email_kampus')->first();
            $email = $cek1->email_kampus;
        }
        // dd($email);
        if($cek1==null){
            return redirect()->back()->with('alert',['title' => 'Information','message' => 'User Sudah Tidak Terdaftar sebagai Dosen, Tendik, Dan Mahasiswa !','status' => 'warning']);
        }

        $in = array(
            'nik' => $nik,
            'email' => $email,
            'role_access' => $role,
            'password' => Hash::make(defaultpassword()),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => session('session')['user_nik']
        );

        $insert = User::insert($in);

        if($insert){
            $alert = ['title' => 'Information','message' => 'User Berhasil Ditambahkan !','status' => 'success'];
        }else{
            $alert = ['title' => 'Information','message' => 'User Gagal Ditambahkan !','status' => 'error'];
        }
            // return redirect()->back()->with('alert',$alert);
            return $alert;
    }

    public function DetailUser($params)
    {

        $id = decrypt($params);
        $cek = User::where('id',$id)->where('isactive',1)->selectRaw('id,nik,role_access')->first();
        // dd($cek);

        $nama = null;
        if($cek->role_access==4){
            $cek1 = MahasiswaModel::where('nim',$cek->nik)->selectRaw('nim as nik, nama')->first();
            $nama = $cek1->nama;
        }else{
            $cek1 = PegawaiModel::where('nip',$cek->nik)->selectRaw('nip as nik, nama')->first();
            $nama = $cek1->nama;
        }
        $data['nik'] = $cek->nik;
        $data['nama'] = $nama;
        $data['role'] = $cek->role_access;
        $data['userid'] = $params;
        return response()->json($data, Response::HTTP_OK);
    }

    public function EditSaveUser($post)
    {
        $id = decrypt($post->userid);

        $up = array(
            'role_access' => $post->roleaccess,
            'updated_at'  => date('Y-m-d H:i:s'),
            'updated_by'  => session('session')['user_nik']
        );

        $updt = User::where('id',$id)->update($up);
        if($updt){
            $alert = ['title' => 'Information','message' => 'Role Akses Berhasil diganti !','status' => 'success'];
        }else{
            $alert = ['title' => 'Information','message' => 'Role Akses Gagal diganti !','status' => 'error'];
        }

        return $alert;
    }

    public function DeleteUser($params)
    {
        $id = decrypt($params);
        // dd($id);
        $cek = User::where('id',$id)->first();
        if(session('session')['user_nik']==$cek->nik){
            $alert = ['title' => 'Gagal','message' => 'User Masih Aktif !','status' => 'error'];
            return response()->json($alert, Response::HTTP_OK);
        }
         $update = User::where('id',$id)->update([
            'isactive' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session('session')['user_nik']
         ]);

        if($update){
            $alert = ['title' => 'Berhasil','message' => 'User Berhasil dihapus','status' => 'success'];
        }else{
            $alert = ['title' => 'Gagal','message' => 'User Gagal dihapus','status' => 'error'];
        }
        return response()->json($alert, Response::HTTP_OK);
    }
    //END User Management

    //User Reset
    public function UserReset()
    {
        // dd(session('_token'));
        $data = array(
            'title' => 'User Reset',
            'menu'  => 'User Reset',
        );

        return view('system::setting/userreset', $data);
    }

    public function UserReset_TablePegawai()
    {
        $cek = MasterGroupModel::whereNotIn('NamaGroup',['MAHASISWA'])->select('KodeGroupUser')->get();
        $aa = array();

        foreach($cek as $q){
            $aa[] = $q->KodeGroupUser;
        }
        $data = User::whereIn('role_access',$aa)->where('isactive',1)->selectRaw('id,nik,email,role_access')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nip', function ($d) {
                return $d->nik;
            })
            ->addColumn('nama', function ($d) {
                $cek = PegawaiModel::where('nip',$d->nik)->where('email_kampus',$d->email)->select('nama')->first();
                $nama = $cek==null ? '-' : $cek->nama;
                return $nama;
            })
            ->addColumn('email', function ($d) {
                return $d->email;
            })
            ->addColumn('role', function ($d) {
                $cek = MasterGroupModel::where('KodeGroupUser',$d->role_access)->selectRaw('NamaGroup')->first();
                return $cek->NamaGroup;
            })
            ->addColumn('action', function ($d) {
                $id = encrypt($d->id);
                $cek = PegawaiModel::where('nip',$d->nik)->where('email_kampus',$d->email)->select('nama')->first();
                $nama = $cek==null ? '-' : $cek->nama;
                $edit = '<a href="'.route('UserReset.ResetPassword',[$id]).'" class="btn_edit"><i title="Reset Password : '.$nama.'" class="fa fa-key text-orange actiona"></i></a>';
                $delete = '<a href="'.route('UserReset.ResetQA',[$id]).'" class="btn_delete"><i title="Reset Security Question : '.$nama.'" class="fa fa-question-circle text-blue actiona"></i></a>';

                return $edit . '  ' . $delete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function UserReset_TableMahasiswa()
    {
        $cek = MasterGroupModel::where('NamaGroup','MAHASISWA')->selectRaw('KodeGroupUser')->first();
        $data = User::where('role_access',$cek->KodeGroupUser)->where('isactive',1)->selectRaw('id,nik,email,role_access')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nim', function ($d) {
                return $d->nik;
            })
            ->addColumn('nama', function ($d) {
                $cek = MahasiswaModel::where('nim',$d->nik)->where('email',$d->email)->select('nama')->first();
                $nama = $cek==null ? '-' : $cek->nama;

                return $nama;
            })
            ->addColumn('email', function ($d) {
                return $d->email;
            })
            ->addColumn('role', function ($d) {
                $cek = MasterGroupModel::where('KodeGroupUser',$d->role_access)->selectRaw('NamaGroup')->first();
                return $cek->NamaGroup;
            })
            ->addColumn('action', function ($d) {
                $id = encrypt($d->id);
                $cek = PegawaiModel::where('nip',$d->nik)->where('email_kampus',$d->email)->select('nama')->first();
                $nama = $cek==null ? '-' : $cek->nama;
                $edit = '<a href="'.route('UserReset.ResetPassword',[$id]).'" class="btn_edit"><i title="Reset Password : '.$nama.'" class="fa fa-key text-orange actiona"></i></a>';
                $delete = '<a href="'.route('UserReset.ResetQA',[$id]).'" class="btn_delete"><i title="Reset Security Question : '.$nama.'" class="fa fa-question-circle text-blue actiona"></i></a>';

                return $edit . '  ' . $delete;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function ResetPassword($params)
    {
        // dd($params);
        $id = decrypt($params);
        $cek = User::where('id',$id)->first();
        if($cek==null){
            $alert = ['title' => 'Gagal','message' => 'User Tidak Terdaftar','status' => 'error'];
            return redirect()->back()->with('alert',$alert);
        }

        $update = User::where('id',$id)->update([
            'password' => Hash::make(defaultpassword()),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session('session')['user_nik']
        ]);

        if($update){
            $logreset = array(
                'email' => $cek->email,
                'token' => session('_token'),
                'activity' => 'Reset Password',
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => session('session')['user_nik']
            );
            UserResetPasswordModel::insert($logreset);
            $alert = ['title' => 'Berhasil','message' => 'Password Berhasil direset','status' => 'success'];
        }else{
            $alert = ['title' => 'Gagal','message' => 'Password Gagal direset','status' => 'error'];
        }
        return redirect()->back()->with('alert',$alert);
    }

    public function ResetQA($params)
    {
        $id = decrypt($params);
        $cek = User::where('id',$id)->first();
        if($cek==null){
            $alert = ['title' => 'Gagal','message' => 'User Tidak Terdaftar','status' => 'error'];
            return redirect()->back()->with('alert',$alert);
        }

        $update = User::where('id',$id)->update([
            'q1' => null,
            'a1' => null,
            'q2' => null,
            'a2' => null,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session('session')['user_nik']
        ]);

        if($update){
            $logreset = array(
                'email' => $cek->email,
                'token' => session('_token'),
                'activity' => 'Reset QA',
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => session('session')['user_nik']
            );
            UserResetPasswordModel::insert($logreset);
            $alert = ['title' => 'Berhasil','message' => 'Pertanyaan Keamanan Berhasil direset','status' => 'success'];
        }else{
            $alert = ['title' => 'Gagal','message' => 'Pertanyaan Keamanan Gagal direset','status' => 'error'];
        }
        return redirect()->back()->with('alert',$alert);
    }
    //END User Reset

    //List Menu Akses
    public function ShowMenu()
    {
        $data = array(
            'title' => 'List Menu',
            'menu'  => 'List Menu',
        );

        return view('system::setting/listmenu', $data);
    }

    public function table_menu()
    {
        $data = ModulModel::get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('modul', function ($d) {
                return $d->modul;
            })
            ->addColumn('menu', function ($d) {
                $nama = $d->menu;
                return $nama;
            })
            ->addColumn('alias', function ($d) {
                return $d->alias;
            })
            ->addColumn('aktif', function ($d) {
                $role = '-';
                $warna = '';
                if($d->MenuAktif=='Y'){
                    $role = 'Aktif';
                    $warna = 'success';
                }else{
                    $role = 'Tidak Aktif';
                    $warna = 'danger';
                }
                $show = '<span class="badge bg-'.$warna.'">'.$role.'</span>';
                return $show;
            })
            ->addColumn('action', function ($d) {
                $id = encrypt($d->IdMenu);
                $url = '#';
                $edit   = '<a href="#" data-id="'.$id.'" class="btn_edit"><i title="Edit Menu" class="fa fa-edit text-orange"></i></a>';
                $aktif = '';
                if($d->MenuAktif=='Y'){
                    $url = route('menu.DeleteAktif',[$id,encrypt('N')]);
                    $aktif = '<a href="'.$url.'" class="btn_delete"><i title="Hapus Menu" class="fa fa-trash text-red"></i></a>';
                }else{
                    $url = route('menu.DeleteAktif',[$id,encrypt('Y')]);
                    $aktif  = '<a href="'.$url.'" class="btn_delete"><i title="Aktifkan Menu" class="fas fa-check-circle text-green"></i></a>';
                }
                return $edit.' '.$aktif;
            })
            ->rawColumns(['action','aktif'])
            ->make(true);
    }

    public function GetMenu($params)
    {
        $id = decrypt($params);
        // dd($id);
        $check = ModulModel::where('IdMenu',$id)->first();

        if($check){
            $data['hasil'] = 1;
            $data['menu'] = $check;
            $data['menuid'] = $params;
        }else{
            $data['hasil'] = 0;
            $data['menu'] = $check;
            $data['menuid'] = $params;
        }
        return response()->json($data, Response::HTTP_OK);
    }

    public function SaveUpdateMenu(Request $post)
    {
        // dd($post);
        $id = isset($post->menuid) ? decrypt($post->menuid) : null;
        $modul = $post->modul;
        $menu = $post->menu;
        $alias = $post->alias;
        $aktif = $post->aktif;

        $check = ModulModel::where('menu','LIKE','%'.$menu.'%')->first();
        if($check){
            $alert = ['title' => 'Gagal','message' => 'Menu Sudah Ada','status' => 'warning'];
            return redirect()->back()->with('alert',$alert);
        }else{
            $in = false;
            if($id){
                $up = array(
                    'modul' => $modul,
                    'menu' => $menu,
                    'alias' => $alias,
                    'MenuAktif' => $aktif,
                    'updated_at' => date('Y_m-d H:i:s'),
                    'updated_by' => session('session')['user_nik']
                );
                $in = ModulModel::where('IdMenu',$id)->update($up);
                $mesage = 'Menu Berhasil Diperbarui';
            }else{
                $up = array(
                    'modul' => $modul,
                    'menu' => $menu,
                    'alias' => $alias,
                    'MenuAktif' => $aktif,
                    'created_at' => date('Y_m-d H:i:s'),
                    'created_by' => session('session')['user_nik']
                );
                $in = ModulModel::insert($up);
                $mesage = 'Menu Berhasil Disimpan';
            }
            if($in){
                $alert = ['title' => 'Berhasil','message' => $mesage,'status' => 'success'];
            }else{
                $alert = ['title' => 'Gagal','message' => 'Menu Gagal Ditambahkan','status' => 'error'];
            }
            return redirect()->back()->with('alert',$alert);
        }
    }

    public function DeleteMenu($params1,$params2)
    {
        $id = decrypt($params1);
        $aktif = decrypt($params2);
        $cekmenu = ModulModel::where('IdMenu',$id)->selectRaw('modul,menu,alias')->first();
        $cek = GroupUserModel::where('Modul',$cekmenu->modul)->where('Menu',$cekmenu->menu)->first();
        // dd($cek);
        if($cek){
            $alert = ['title' => 'Gagal','message' => 'Menu Sudah Digunakan !','status' => 'error'];
            return redirect()->back()->with('alert',$alert);
        }
        $up = array(
            'MenuAktif' => $aktif,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => session('session')['user_nik']
        );

        $update = ModulModel::where('IdMenu',$id)->update($up);

        if($update){
            $alert = ['title' => 'Berhasil','message' => 'Menu Berhasil Diperbarui','status' => 'success'];
        }else{
            $alert = ['title' => 'Gagal','message' => 'Menu Gagal Diperbarui','status' => 'error'];
        }
        return redirect()->back()->with('alert',$alert);

    }
    //END Menu Akses

    //Group User
    public function ShowGroupUser()
    {
        $data = array(
            'title' => 'Master Group User',
            'menu'  => 'Master Data Group User',
        );

        return view('system::setting/groupuser', $data);
    }

    public function table_groupuser()
    {
        $data = MasterGroupModel::where('isactive',1)->with('groupuser')->orderby('NamaGroup')->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('group', function ($d) {
                return $d->NamaGroup;
            })
            ->addColumn('privilege', function ($d) {
                $nama = $d->groupuser->count().' Menu';
                return $nama;
            })
            ->addColumn('action', function ($d) {
                $id = encrypt($d->KodeGroupUser);
                $url = '#';
                $editpriv  = '<a href="'.route('gruopuser.ShowPrivilege',[$id]).'" class="btn_delete"><i title="Edit Privilege of '.$d->NamaGroup.'" class="fa fa-eye text-green"></i></a>';
                $editnama   = '<a href="#" data-id="'.$id.'" class="btn_edit"><i title="Edit '.$d->NamaGroup.' Data" class="fa fa-edit text-orange"></i></a>';

                return $editpriv.'  '.$editnama;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function SaveUpdateGroupUser(Request $post)
    {
        $id = isset($post->groupuserid) ? decrypt($post->groupuserid) : null;
        $groupuser = $post->groupuser;

        $check = MasterGroupModel::where('NamaGroup','LIKE','%'.$groupuser.'%')->first();
        if($check){
            $alert = ['title' => 'Gagal','message' => 'Nama Sudah Ada !','status' => 'warning'];
            return redirect()->back()->with('alert',$alert);
        }else{
            $in = false;
            if($id){
                $up = array(
                    'NamaGroup' => $groupuser,
                    'updated_at' => date('Y_m-d H:i:s'),
                    'updated_by' => session('session')['user_nik']
                );
                $in = MasterGroupModel::where('KodeGroupUser',$id)->update($up);
                $mesage = 'Group User Berhasil Diperbarui';
            }else{
                $lastdata = MasterGroupModel::orderBy('KodeGroupUser', 'desc')->first();
                $format = '000';
                $lastid = substr($lastdata->KodeGroupUser, 1) + 1;
                $kdgroupuser = 'G' . substr($format, strlen($lastid)) . $lastid;
                $up = array(
                    'KodeGroupUser' => $kdgroupuser,
                    'NamaGroup' => $groupuser,
                    'created_at' => date('Y_m-d H:i:s'),
                    'created_by' => session('session')['user_nik']
                );
                $in = MasterGroupModel::insert($up);
                $mesage = 'Group User Berhasil Disimpan';
            }
            if($in){
                $alert = ['title' => 'Berhasil','message' => $mesage,'status' => 'success'];
            }else{
                $alert = ['title' => 'Gagal','message' => 'Group User Gagal Ditambahkan','status' => 'error'];
            }
            return redirect()->back()->with('alert',$alert);
        }
    }

    public function GetGroupUser($params)
    {
        $id = decrypt($params);
        // dd($id);
        $check = MasterGroupModel::where('KodeGroupUser',$id)->first();

        if($check){
            $data['hasil'] = 1;
            $data['group'] = $check;
            $data['groupid'] = $params;
        }else{
            $data['hasil'] = 0;
            $data['menu'] = $check;
            $data['groupid'] = $params;
        }
        return response()->json($data, Response::HTTP_OK);
    }

    public function DeleteGroupUser($params)
    {
        $id = decrypt($params);
        dd($id);
    }

    public function ShowPrivilege($params)
    {
        $id = decrypt($params);
        $data = MasterGroupModel::where('KodeGroupUser',$id)->first();
        $modul = ModulModel::where('MenuAktif','Y')->select('modul')->distinct()->orderBy('modul')->get();
        // dd($modul);
        $moduldata = null;

        foreach ($modul as $m)
        {
            $moduldata[$m->modul] = ModulModel::where('modul', $m->modul)->where('MenuAktif','Y')->orderBy(DB::raw('modul, menu','alias'))->get();
        }

        if($data)
        {
            $mod_groupuser = GroupUserModel::where('KodeGroupUser', $id)->select('Modul','Menu','FullAkses')->get();
            $dataku[] = '';
            foreach ($mod_groupuser as $key => $value) {
                $dataku[] = $value->Modul.$value->Menu;
            }

            $view = array(
                'title' => 'Privilege Menu',
                'menu' => 'Privilege Menu',
                'data' => $data,
                'id' => $params,
                'modul'=>$modul,
                'moduldata' => $moduldata,
                'm_modgroup' => $dataku,
                'akses' => $mod_groupuser
            );
            // dd($view);
            return view('system::setting/privilege', $view);
        }

        return redirect()->back()->with('alert', [
            'title' => 'Error!',
            'message' => 'Data Not Found!',
            'status' => 'error'
        ]);
    }

    function StorePrivilege(Request $request, $params)
    {
        $decrypted = decrypt($params);
        $master = MasterGroupModel::where('KodeGroupUser',$decrypted)->first();
        DB::beginTransaction();

        $del = GroupUserModel::where('KodeGroupUser',$decrypted)->delete();
        $moduldata = ModulModel::where('MenuAktif','Y')->orderBy(DB::raw('modul, menu','alias'))->select('modul','menu')->get();
        // dd($moduldata);
        foreach ($moduldata as $key => $value) {
            $cari = $value->modul.$value->menu;
            // dd($cari);
            if(isset($request->menumod[$cari]) AND $request->actionmod[$cari]!=null){
                echo $request->menumod[$cari].' vs '.$request->actionmod[$cari].'<br>';
                $mod = explode("#", $request->menumod[$cari]);

                $inn = array(
                    'KodeGroupUser'=>$decrypted,
                    'Modul'=>$mod[0],
                    'Menu'=>$mod[1],
                    'FullAkses'=>$request->actionmod[$cari],
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>session('session')['user_nik']
                );
                // dd($inn);
                $in = GroupUserModel::insert($inn);
                if(!$in){
                    $gagal[] = 'ok';
                }
            }
        }
        if(empty($gagal)){
                DB::commit();
            return redirect(route('gruopuser.show'))->with('alert', [
                'title' => 'Success!',
                'message' => 'Data Has Been Saved Successfully!',
                'status' => 'success'
            ]);

        }else{
            DB::rollBack();
            return redirect(route('gruopuser.show'))->with('alert', [
                'title' => 'Error!',
                'message' => 'Data Failed Saved!',
                'status' => 'error'
            ]);
        }

    }
    //END Group User

}
