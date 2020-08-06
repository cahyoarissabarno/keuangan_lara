<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //pengaturan bahasa faker
        $faker=Faker::create('id_ID');

        //input data dummy sebanyak perulangan
        for($x=1;$x<=20;$x++)
        {
            //data dummy
            $tgl_hari_ini=date('Y-m-d');
            $jenis = $faker->randomElement(["Pemasukan","Pengeluaran"]);
            $kategori = $faker->randomElement(["4","5","6","7","8"]);
            $nominal = $faker->randomElement([
                "100000","200000","300000","500000","1000000","15000000"
            ]);

            $keterangan ="";

            //insert db
            DB::table('transaksi')->insert([
                'tanggal'=>$tgl_hari_ini,
                'jenis'=>$jenis,
                'kategori_id'=>$kategori,
                'nominal'=>$nominal,
                'keterangan'=>$keterangan
            ]);
        }
    }
}
