{{--
    Splash logo: hanya tampil sekali per sesi tab (sessionStorage).
    - Kelas "no-splash" dipasang di <html> oleh skrip di <head> (layouts/base) sebelum halaman digambar,
      jadi kunjungan berikutnya tidak berkedip.
    - Hilang begitu DOM siap, tapi tampil minimal ~1,2 dtk supaya logonya sempat terlihat.
    - Pengaman: animasi CSS menyembunyikannya sendiri setelah 3,5 dtk walau JavaScript gagal.
--}}
<div id="splash" aria-hidden="true">
    <img src="{{ asset('images/logo-www.png') }}" alt="" class="splash-logo" width="120" height="48">
    <span class="splash-bar"><span></span></span>
</div>
<noscript><style>#splash { display: none !important; }</style></noscript>
<script>
    (function () {
        var splash = document.getElementById('splash');
        if (!splash || document.documentElement.classList.contains('no-splash')) return;

        try { sessionStorage.setItem('www-splash-seen', '1'); } catch (e) {}

        var start = Date.now();
        var hide = function () {
            var wait = Math.max(0, 1200 - (Date.now() - start));
            setTimeout(function () {
                splash.classList.add('is-hidden');
                setTimeout(function () { splash.remove(); }, 400);
            }, wait);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hide);
        } else {
            hide();
        }
    })();
</script>
