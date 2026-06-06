<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->nama_product }} - WearWoreWorn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body class="bg-background text-foreground min-h-screen flex flex-col">

    <nav class="bg-gray-800 border-b border-gray-700 p-4 sticky top-0 z-50 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <a href="/" class="bg-white text-black font-bold text-xl px-4 py-1 uppercase rounded-sm tracking-wider">
                    LOGO
                </a>
            </div>
            
            <div class="hidden md:flex space-x-8 font-semibold text-gray-300">
                <a href="/" class="hover:text-white transition">CATALOG</a>
                <a href="#" class="hover:text-white transition">ABOUT US</a>
            </div>

            <div class="flex items-center space-x-4">
                <a href="#" class="text-gray-300 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </a>
                
                @auth
                    <a href="{{ route('home') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded transition">Account</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white font-semibold transition">Log In</a>
                    <a href="{{ route('register') }}" class="bg-white hover:bg-gray-200 text-black font-bold px-4 py-2 rounded transition">Sign Up</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-grow container mx-auto p-4 md:p-8 mt-4">
        <div class="flex flex-col lg:flex-row gap-10">
            
            <div class="w-full lg:w-1/2 flex flex-col gap-4">
                <div class="bg-gray-800 rounded-lg aspect-square flex items-center justify-center overflow-hidden border border-gray-700 shadow-lg">
                    @php
                        $mainImage = $product->images->first() ? asset('images/' . $product->images->first()->url_gambar) : 'https://dummyimage.com/800x800/374151/fff&text=No+Image';
                    @endphp
                    <img id="main-image" src="{{ $mainImage }}" onerror="this.onerror=null; this.src='https://dummyimage.com/800x800/374151/fff&text=No+Image';" class="w-full h-full object-cover">
                </div>
                
                <div class="grid grid-cols-5 gap-4">
                    @foreach($product->images as $index => $image)
                    <button type="button" 
                            class="thumbnail-btn bg-gray-800 rounded-md aspect-square border {{ $index === 0 ? 'border-white border-2' : 'border-gray-700' }} hover:border-gray-500 overflow-hidden" 
                            data-src="{{ asset('images/' . $image->url_gambar) }}">
                        <img src="{{ asset('images/' . $image->url_gambar) }}" 
                             onerror="this.onerror=null; this.src='https://dummyimage.com/150x150/4b5563/fff&text=Broken';" 
                             class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="w-full lg:w-1/2 flex flex-col justify-start">
                <form action="{{ url('/cart/add/' . $product->id_product) }}" method="POST">
                    @csrf
                    
                    <h1 class="text-4xl font-extrabold text-white mb-2 uppercase">{{ $product->nama_product }}</h1>
                    <p class="text-2xl font-semibold text-gray-300 mb-8">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>

                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-3">
                            <span class="font-bold text-white text-lg">Size</span>
                            <button type="button" class="text-sm font-semibold text-blue-400 hover:text-blue-300 transition">Size Guide ></button>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @forelse($variants as $variant)
                            <label class="cursor-pointer">
                                <input type="radio" name="size" value="{{ $variant->nama_size }}" class="peer hidden size-radio">
                                <span class="inline-flex items-center justify-center min-w-[3.5rem] h-12 px-4 rounded border border-gray-600 text-gray-300 bg-gray-800 peer-checked:bg-white peer-checked:text-black peer-checked:border-white hover:bg-gray-700 transition-colors font-bold text-lg">
                                    {{ $variant->nama_size }}
                                </span>
                            </label>
                            @empty
                            <span class="text-red-400 font-semibold">Varian ukuran belum tersedia.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="mb-8 flex items-center gap-6">
                        <div class="flex items-center border border-gray-600 rounded bg-gray-800 h-12 w-32">
                            <button type="button" id="btn-minus" class="w-1/3 h-full flex items-center justify-center text-gray-400 hover:text-white transition font-bold text-xl">-</button>
                            <input type="number" id="qty-input" name="quantity" value="1" min="1" max="99" class="w-1/3 h-full text-center bg-transparent text-white font-bold focus:outline-none" readonly>
                            <button type="button" id="btn-plus" class="w-1/3 h-full flex items-center justify-center text-gray-400 hover:text-white transition font-bold text-xl">+</button>
                        </div>
                        <div class="text-gray-400 text-lg">
                            Stok: <span id="stock-display" class="font-bold text-white">{{ $totalStock }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 mb-10 border-b border-gray-700 pb-10">
                        <button type="submit" class="w-full bg-gray-200 hover:bg-white text-black font-bold py-4 px-4 rounded transition duration-200 text-lg">
                            Add to Cart
                        </button>
                        <button type="button" class="w-full bg-gray-700 hover:bg-gray-600 border border-gray-600 text-white font-bold py-4 px-4 rounded transition duration-200 text-lg">
                            Buy it now
                        </button>
                    </div>

                    <div>
                        <h3 class="font-bold text-xl text-white mb-3">Description</h3>
                        <p class="text-gray-400 leading-relaxed text-base whitespace-pre-line">{{ $product->deskripsi }}</p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnMinus = document.getElementById('btn-minus');
            const btnPlus = document.getElementById('btn-plus');
            const qtyInput = document.getElementById('qty-input');

            btnMinus.addEventListener('click', function() {
                let currentValue = parseInt(qtyInput.value);
                if (currentValue > 1) qtyInput.value = currentValue - 1;
            });

            btnPlus.addEventListener('click', function() {
                let currentValue = parseInt(qtyInput.value);
                if (currentValue < 99) qtyInput.value = currentValue + 1;
            });

            const thumbnails = document.querySelectorAll('.thumbnail-btn');
            const mainImage = document.getElementById('main-image');

            thumbnails.forEach(btn => {
                btn.addEventListener('click', function() {
                    mainImage.src = this.getAttribute('data-src');
                    thumbnails.forEach(t => {
                        t.classList.remove('border-white', 'border-2');
                        t.classList.add('border-gray-700', 'border');
                    });
                    this.classList.remove('border-gray-700', 'border');
                    this.classList.add('border-white', 'border-2');
                });
            });

            const stockDisplay = document.getElementById('stock-display');
            const sizeRadios = document.querySelectorAll('.size-radio');
            let selectedRadio = null;
            
            const totalStock = {{ $totalStock }};
            const stockData = {
                @foreach($variants as $variant)
                    "{{ $variant->nama_size }}": {{ $variant->stok }},
                @endforeach
            };

            sizeRadios.forEach(radio => {
                radio.addEventListener('click', function(e) {
                    if (selectedRadio === this) {
                        this.checked = false;
                        selectedRadio = null;
                        stockDisplay.innerText = totalStock;
                    } else {
                        selectedRadio = this;
                        stockDisplay.innerText = stockData[this.value] || 0;
                    }
                });
            });
        });
    </script>
</body>
</html>