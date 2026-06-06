<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Berhasil</title>
</head>
<body style="text-align: center;">
    <h1>PESANAN BERHASIL DIBUAT!</h1>

    <div style="font-size: 50px; color: green;">
        &#10004;
    </div>

    <p>Total Transfer:</p>
    <h2>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</h2>

    <div style="margin: 20px auto; padding: 20px; border: 1px solid #ccc; display: inline-block;">
        <strong>{{ $infoRekening }}</strong>
    </div>

    <p style="color: orange;">Menunggu Pembayaran...</p>

    <div style="margin-top: 15px;">
        <span style="font-size: 18px; color: red;">Batas waktu pembayaran: </span>
        <strong id="countdown-timer" style="font-size: 24px; color: red;">Menghitung...</strong>
    </div>

    <br><br>
    <a href="{{ route('home') }}">Kembali ke Beranda</a>

    <script>
        // Ambil waktu batas pembayaran dari server (dikonversi ke timestamp JavaScript)
        // Menggunakan getTimestamp() * 1000 agar akurat secara timezone
        const deadline = {{ $batasPembayaran->getTimestamp() * 1000 }};
        
        const timerElement = document.getElementById('countdown-timer');

        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = deadline - now;

            if (distance < 0) {
                clearInterval(x);
                timerElement.innerHTML = "WAKTU HABIS";
                return;
            }

            // Kalkulasi jam, menit, dan detik
            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Format dengan angka 0 di depan jika di bawah 10
            const displayHours = hours.toString().padStart(2, '0');
            const displayMinutes = minutes.toString().padStart(2, '0');
            const displaySeconds = seconds.toString().padStart(2, '0');

            timerElement.innerHTML = displayHours + " Jam " + displayMinutes + " Menit " + displaySeconds + " Detik";
            
        }, 1000);
    </script>
</body>
</html>
