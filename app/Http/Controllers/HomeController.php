<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kategori;
use App\Transaksi;
use App\User;
use Hash;
use Auth;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $tanggal_hari_ini=date('Y-m-d');
        $bulan_ini=date('m');
        $tahun_ini=date('Y');

        $pemasukan_hari_ini=Transaksi::where('jenis','Pemasukan')
                                ->whereDate('tanggal',$tanggal_hari_ini)
                                ->sum('nominal');
        $pemasukan_bulan_ini=Transaksi::where('jenis','Pemasukan')
                                ->whereMonth('tanggal',$bulan_ini)
                                ->sum('nominal');
        $pemasukan_tahun_ini=Transaksi::where('jenis','Pemasukan')
                                ->whereYear('tanggal',$tahun_ini)
                                ->sum('nominal');
        $seluruh_pemasukan=Transaksi::where('jenis','Pemasukan')
                                ->sum('nominal');

        $pengeluaran_hari_ini=Transaksi::where('jenis','Pengeluaran')
                                ->whereDate('tanggal',$tanggal_hari_ini)
                                ->sum('nominal');
        $pengeluaran_bulan_ini=Transaksi::where('jenis','Pengeluaran')
                                ->whereMonth('tanggal',$bulan_ini)
                                ->sum('nominal');
        $pengeluaran_tahun_ini=Transaksi::where('jenis','Pengeluaran')
                                ->whereYear('tanggal',$tahun_ini)
                                ->sum('nominal');
        $seluruh_pengeluaran=Transaksi::where('jenis','Pengeluaran')
                                ->sum('nominal');
        return view('home',
                [
                    'pemasukan_hari_ini'=>$pemasukan_hari_ini,
                    'pemasukan_bulan_ini'=>$pemasukan_bulan_ini,
                    'pemasukan_tahun_ini'=>$pemasukan_tahun_ini,
                    'seluruh_pemasukan'=>$seluruh_pemasukan,
                    'pengeluaran_hari_ini'=>$pengeluaran_hari_ini,
                    'pengeluaran_bulan_ini'=>$pengeluaran_bulan_ini,
                    'pengeluaran_tahun_ini'=>$pengeluaran_tahun_ini,
                    'seluruh_pengeluaran'=>$seluruh_pengeluaran
                ]
        );
    }

    public function kategori()
    {
        $kategori=Kategori::all();
        return view('kategori',['kategori'=>$kategori]);
    }

    public function kategori_tambah()
    {
        return view('kategori_tambah');
    }

    public function kategori_aksi(Request $data)
    {
        $data->validate([
            'kategori'=>'required'
        ]);

        $kategori=$data->kategori;
        Kategori::insert([
            'kategori'=>$kategori
        ]);

        return redirect('kategori')->with("sukses","kategori berhasil tersimpan");

    }

    public function kategori_edit($id)
    {
        $kategori=Kategori::find($id);

        return view('kategori_edit',['kategori'=>$kategori]);

    }

    public function kategori_update($id, Request $data)
    {
        $data->validate([
            'kategori'=>'required'
        ]);

        $nama_kategori = $data->kategori;
        $kategori = Kategori::find($id);
        $kategori->Kategori=$nama_kategori;
        $kategori->save();

        return redirect('kategori')->with("sukses","Kategori Berhasi Diubah");
    }

    public function kategori_hapus($id)
    {
        $kategori=Kategori::find($id);
        $kategori->delete();

        $transaksi=Transaksi::where('kategori_id',$id);
        $transaksi->delete();

        return redirect('kategori')->with("sukses","Kategori berhasil dihapus");

    }

    public function transaksi()
    {
        $transaksi = Transaksi::orderBy('id','desc')->paginate(6);
         
        return view('transaksi',['transaksi'=>$transaksi]);
    }

    public function transaksi_tambah()
    {
        $kategori= Kategori::all();
        return view('transaksi_tambah',['kategori'=>$kategori]);
    }

    public function transaksi_aksi(Request $data)
    {
        $data->validate([
            'tanggal'=> 'required',
            'jenis'=> 'required',
            'kategori'=> 'required',
            'nominal'=> 'required'
        ]);

        Transaksi::insert([
            'tanggal'=>$data->tanggal,
            'jenis'=>$data->jenis,
            'kategori_id'=>$data->kategori,
            'nominal'=>$data->nominal,
            'keterangan'=>$data->keterangan 
        ]);

        return redirect('transaksi')->with("sukses","Transaksi berhasil ditambahkan");

    }

    public function transaksi_edit($id)
    {
        $kategori=Kategori::all();
        $transaksi=Transaksi::find($id);

        return view('transaksi_edit',['kategori'=>$kategori, 'transaksi'=>$transaksi]);
        
    }

    public function transaksi_update($id, Request $data)
    {
        $data->validate([
            'tanggal'=>'required',
            'jenis'=>'required',
            'kategori'=>'required',
            'nominal'=>'required'
        ]);

        $transaksi=Transaksi::find($id);

        $transaksi->tanggal=$data->tanggal;
        $transaksi->jenis=$data->jenis;
        $transaksi->kategori_id=$data->kategori;
        $transaksi->nominal=$data->nominal;
        $transaksi->keterangan=$data->keterangan;

        $transaksi->save();

        return redirect('transaksi')->with("sukses","Transaksi berhasil diubah");

    }

    public function transaksi_hapus($id)
    {
        $transaksi=Transaksi::find($id);
        $transaksi->delete();

        return redirect('transaksi')->with("sukses","Transaksi Berhasil Dihapus");
        
    }

    public function transaksi_cari(Request $data)
    {
        $cari=$data->cari;
        $transaksi=Transaksi::orderBy('id','desc')
                    ->where('jenis','like',"%".$cari."%")
                    ->orWhere('tanggal','like',"%".$cari."%")
                    ->orWhere('keterangan','like',"%".$cari."%")
                    ->orWhere('nominal','like',"%".$cari."%")
                    ->paginate(6);

        $transaksi->appends($data->only('cari'));
        return view('transaksi',['transaksi'=>$transaksi]);
        
    }

    public function laporan()
    {
        $kategori=Kategori::all();
        return view('laporan',['kategori'=>$kategori]);

    }

    public function laporan_hasil(Request $data)
    {
        $data->validate([
            'dari'=>'required',
            'sampai'=>'required'
        ]);

        $kategori=Kategori::all();

        $dari=$data->dari;
        $sampai=$data->sampai;
        $id_kategori=$data->kategori;
        
        if ($id_kategori=="semua") {
            $laporan = Transaksi::whereDate('tanggal','>=',$dari)
                        ->whereDate('tanggal','<=',$sampai)
                        ->orderBy('id','desc')
                        ->get();
        }
        else {
            $laporan = Transaksi::where('kategori_id',$id_kategori)
                        ->whereDate('tanggal','>=',$dari)
                        ->whereDate('tanggal','<=',$sampai)
                        ->orderBy('id','desc')
                        ->get();
        }

        return view('laporan_hasil',[
                        'laporan'=>$laporan,
                        'kategori'=>$kategori,
                        'dari'=>$dari,
                        'sampai'=>$sampai,
                        'kat'=>$id_kategori
                        
                        ]);
        
    }

    public function laporan_print(Request $data)
    {
        $data->validate([
            'dari'=>'required',
            'sampai'=>'required'
        ]);

        $kategori=Kategori::all();

        $dari=$data->dari;
        $sampai=$data->sampai;
        $id_kategori=$data->kategori;
        
        if ($id_kategori=="semua") {
            $laporan = Transaksi::whereDate('tanggal','>=',$dari)
                        ->whereDate('tanggal','<=',$sampai)
                        ->orderBy('id','desc')
                        ->get();
        }
        else {
            $laporan = Transaksi::where('kategori_id',$id_kategori)
                        ->whereDate('tanggal','>=',$dari)
                        ->whereDate('tanggal','<=',$sampai)
                        ->orderBy('id','desc')
                        ->get();
        }

        return view('laporan_print',[
                        'laporan'=>$laporan,
                        'kategori'=>$kategori,
                        'dari'=>$dari,
                        'sampai'=>$sampai,
                        'kat'=>$id_kategori
                        
                        ]);
    }

    public function laporan_excel()
    {
        return Excel::download(new LaporanExport,'laporan.xlsx');
    }

    public function ganti_password()
    {
        return view('gantipassword');
    }

    public function ganti_password_aksi(Request $data)
    {
        if (!(Hash::check($data->get('current-password'),
              Auth::user()->password))) {
            return redirect()->back()->with("error","password sekarang tidak sesuai");
        }
        if (strcmp($data->get('current-password'),
                    $data->get('new-password'))==0) {
            return redirect()->back()->with("error","Password baru tidak boleh sama dengan password sekarang");
        }

        $validateData=$data->validate([
            'current-password'=>'required',
            'new-password'=>'required|string|min:6|confirmed'
        ]);

        $user=Auth::user();
        $user->password=bcrypt($data->get('new-password'));
        $user->save();

        return redirect()->back()->with("sukses","password berhasil diganti");

    }
}
