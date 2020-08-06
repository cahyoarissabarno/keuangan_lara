<?php

namespace App\Exports;

use App\Transaksi;
use App\Kategori;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Transaksi::all();
    }
}

class LaporanExcel implements FromView
{
    public function view():View
    {
        $kategori=Kategori::all();
        $dari=$_GET['dari'];
        $sampai=$GET['sampai'];
        $id_kategori=$_GET['kategori'];

        if ($id_kategori=="semua") {
            $laporan=Transaksi::whereDate('tanggal','>=',$dari)
            ->whereDate('tanggal','<=',$sampai)
            ->orderBy('id','desc')->get();
        }else {
            $laporan=Transaksi::where('kategori_id',$id_kategori)
            ->whereDate('tanggal','>=',$dari)
            ->whereDate('tanggal','<=',$sampai)
            ->orderBy('id','desc')->get();
        }

        return view('laporan_excel',[
            'laporan'=>$laporan,
            'kategori'=>$kategori,
            'dari'=>$dari,
            'sampai'=>$sampai,
            'kat'=>$id_kategori
            
            ]);
    }

}
