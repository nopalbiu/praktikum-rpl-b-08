@php
    $messages = collect([
        ['type' => 'success', 'text' => session('success')],
        ['type' => 'error', 'text' => session('error')],
        ['type' => 'info', 'text' => session('status')],
    ])->filter(fn ($m) => filled($m['text']))->values();
@endphp

{{-- Notifikasi singkat dari session flash (success / error / status). --}}
<div aria-live="polite" class="pointer-events-none fixed inset-x-0 top-20 z-[60] flex flex-col items-center gap-2 px-4 sm:top-auto sm:right-6 sm:bottom-6 sm:left-auto sm:items-end">
    @foreach ($messages as $message)
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, {{ $message['type'] === 'error' ? 7000 : 4000 }})"
             x-show="show"
             x-transition:enter="transition duration-200 ease-out"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:leave="transition duration-150 ease-in"
             x-transition:leave-end="opacity-0"
             role="{{ $message['type'] === 'error' ? 'alert' : 'status' }}"
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border bg-zinc-900/95 p-4 shadow-xl shadow-black/40 backdrop-blur
                    {{ $message['type'] === 'error' ? 'border-red-900/70' : 'border-zinc-800' }}">
            @if ($message['type'] === 'success')
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z" clip-rule="evenodd"/></svg>
            @elseif ($message['type'] === 'error')
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
            @else
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            @endif

            <p class="flex-1 text-sm text-zinc-200">{{ $message['text'] }}</p>

            <button type="button" @click="show = false" class="-m-1 rounded p-1 text-zinc-500 hover:text-white" aria-label="Tutup notifikasi">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 5.3a1 1 0 00-1.4 1.4L8.6 10l-3.7 3.3a1 1 0 101.4 1.4L10 11.4l3.3 3.3a1 1 0 001.4-1.4L11.4 10l3.3-3.3a1 1 0 00-1.4-1.4L10 8.6 6.3 5.3z"/></svg>
            </button>
        </div>
    @endforeach
</div>
