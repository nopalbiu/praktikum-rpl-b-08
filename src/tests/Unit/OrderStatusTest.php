<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_format_status_pesanan_mengubah_underscore_menjadi_spasi_dan_lowercase()
    {
        $statusDariDb = 'MENUNGGU_PEMBAYARAN';
        $expected = 'menunggu pembayaran';

        $result = strtolower(str_replace('_', ' ', $statusDariDb));

        $this->assertEquals($expected, $result);
    }

    public function test_format_status_pesanan_mengembalikan_string_kosong_jika_input_kosong()
    {
        $statusDariDb = '';
        $expected = '';

        $result = strtolower(str_replace('_', ' ', $statusDariDb));

        $this->assertEquals($expected, $result);
    }
}