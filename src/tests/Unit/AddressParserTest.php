<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AddressParserTest extends TestCase
{
    public function test_parsing_alamat_berhasil_mengekstrak_data_pelanggan()
    {
        $rawAlamat = 'Nama: Budi Santoso No. HP: 08123456789 Alamat: Jl. Sudirman No 1, Jakarta';
        $expectedNama = 'Budi Santoso';
        $expectedHp = '08123456789';

        preg_match('/Nama:\s*(.*?)\s*No\. HP:\s*(.*?)\s*Alamat:\s*(.*)/i', $rawAlamat, $matches);
        $namaParsed = $matches[1] ?? '-';
        $hpParsed = $matches[2] ?? '-';

        $this->assertEquals($expectedNama, $namaParsed);
        $this->assertEquals($expectedHp, $hpParsed);
    }

    public function test_parsing_alamat_menggunakan_default_jika_format_berantakan()
    {
        $rawAlamat = 'Jalan Merdeka Barat, Rumah Pagar Hitam'; 
        $expectedNama = '-';

        preg_match('/Nama:\s*(.*?)\s*No\. HP:\s*(.*?)\s*Alamat:\s*(.*)/i', $rawAlamat, $matches);
        $namaParsed = $matches[1] ?? '-';

        $this->assertEquals($expectedNama, $namaParsed);
    }
}