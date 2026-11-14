{{-- Dialog konfirmasi global. Dipicu oleh <form data-confirm="Pesan…" data-confirm-title="…" data-confirm-button="…"> (lihat resources/js/app.js). --}}
<div x-data="{ show: false, title: '', message: '', button: 'Ya, lanjutkan', run: null,
              open(d) { this.title = d.title; this.message = d.message; this.button = d.button; this.run = d.onConfirm; this.show = true; $nextTick(() => $refs.cancel.focus()) },
              accept() { this.show = false; this.run && this.run() } }"
     @confirm-dialog.window="open($event.detail)"
     @keydown.escape.window="show = false">
    <div x-cloak x-show="show" x-transition.opacity class="fixed inset-0 z-[70] flex items-end justify-center bg-black/60 p-4 sm:items-center"
         @click.self="show = false" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
        <div x-show="show" x-transition class="w-full max-w-sm rounded-2xl border border-zinc-800 bg-zinc-900 p-6 shadow-2xl">
            <h2 id="confirm-title" class="text-base font-semibold text-white" x-text="title"></h2>
            <p class="mt-2 text-sm text-zinc-400" x-text="message"></p>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" x-ref="cancel" @click="show = false" class="btn btn-ghost">Batal</button>
                <button type="button" @click="accept()" class="btn btn-danger" x-text="button"></button>
            </div>
        </div>
    </div>
</div>
