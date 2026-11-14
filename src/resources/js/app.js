import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Konfirmasi sebelum submit: <form data-confirm="Pesan"> memunculkan dialog <x-confirm-dialog>.
// Didaftarkan di fase capture supaya jalan sebelum penguncian tombol di bawah.
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form.dataset.confirm || form.dataset.confirmed) return;

    event.preventDefault();
    const submitter = event.submitter;
    window.dispatchEvent(new CustomEvent('confirm-dialog', {
        detail: {
            title: form.dataset.confirmTitle || 'Yakin?',
            message: form.dataset.confirm,
            button: form.dataset.confirmButton || 'Ya, lanjutkan',
            onConfirm: () => {
                form.dataset.confirmed = '1';
                form.requestSubmit(submitter);
            },
        },
    }));
}, true);

// Cegah klik ganda: tombol submit dimatikan setelah form dikirim.
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.dataset.allowResubmit !== undefined || event.defaultPrevented) return;

    // Ditunda satu tick supaya formaction/nilai tombol yang diklik tetap ikut terkirim.
    setTimeout(() => {
        // form.elements juga mencakup tombol di luar <form> yang memakai atribut form="…"
        [...form.elements].filter((el) => el.type === 'submit' && !el.hasAttribute('data-no-lock')).forEach((button) => {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        });
    }, 0);
});

// Tombol kembali (bfcache) bisa memunculkan halaman dengan tombol yang masih terkunci.
window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[aria-busy="true"]').forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    });
});

// ------------------------------------------------------------------
// Progress bar tipis di atas layar saat pindah halaman / kirim form.
// Baru muncul kalau pemuatan lebih dari 150 ms, supaya tidak berkedip.
// ------------------------------------------------------------------
const progress = (() => {
    const bar = document.getElementById('page-progress');
    let showTimer;
    let trickleTimer;
    let value = 0;

    const set = (v) => {
        value = v;
        if (bar) bar.style.width = `${v}%`;
    };

    return {
        start() {
            if (!bar) return;
            clearTimeout(showTimer);
            clearInterval(trickleTimer);
            showTimer = setTimeout(() => {
                bar.classList.add('is-active');
                set(15);
                // Merayap pelan mendekati 90% sampai halaman baru tampil
                trickleTimer = setInterval(() => set(value + (90 - value) * 0.1), 300);
            }, 150);
        },
        reset() {
            if (!bar) return;
            clearTimeout(showTimer);
            clearInterval(trickleTimer);
            bar.classList.remove('is-active');
            set(0);
        },
    };
})();

const isPageNavigation = (link, event) => {
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
    if (link.target && link.target !== '_self') return false;
    if (link.hasAttribute('download')) return false;

    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin) return false;
    // Hanya pindah anchor (#…) di halaman yang sama
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return false;

    return true;
};

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!isPageNavigation(link, event)) return;
    // Dicek setelah handler lain jalan (mis. pagination AJAX di katalog memanggil preventDefault)
    setTimeout(() => { if (!event.defaultPrevented) progress.start(); }, 0);
});

document.addEventListener('submit', (event) => {
    setTimeout(() => { if (!event.defaultPrevented) progress.start(); }, 0);
});

// Kembali lewat tombol back (bfcache) atau navigasi batal: matikan bar
window.addEventListener('pageshow', () => progress.reset());

Alpine.start();
