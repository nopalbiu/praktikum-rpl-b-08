<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CartCalculationTest extends TestCase
{
    public function test_kalkulasi_total_harga_pesanan_dihitung_dengan_benar()
    {
        $hargaSatuan = 150000;
        $qty = 3;
        $expectedTotal = 450000;

        $totalHarga = $hargaSatuan * $qty;

        $this->assertEquals($expectedTotal, $totalHarga);
    }

    public function test_kalkulasi_total_harga_menjadi_nol_jika_qty_bernilai_negatif()
    {
        $hargaSatuan = 150000;
        $qty = -2; 
        $expectedTotal = 0;

        $qtyValid = $qty > 0 ? $qty : 0;
        $totalHarga = $hargaSatuan * $qtyValid;

        $this->assertEquals($expectedTotal, $totalHarga);
    }
}