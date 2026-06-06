<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
</head>
<body>
    <h1>CHECKOUT</h1>

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($hasOutOfStock)
        <div style="color: red;">
            <p>Beberapa item di keranjang memiliki stok yang tidak mencukupi. Silakan kembali ke keranjang.</p>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        @foreach($selectedItems as $itemId)
            <input type="hidden" name="items[]" value="{{ $itemId }}">
        @endforeach
        
        <table border="1" width="100%">
            <tr>
                <td width="50%" valign="top">
                    <h3>SHIPPING ADDRESS</h3>
                    <div>
                        <label>Name</label><br>
                        <input type="text" name="nama_penerima" value="{{ old('nama_penerima', $user->nama) }}" required>
                    </div>
                    <br>
                    <div>
                        <label>No. Handphone</label><br>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}" pattern="[0-9]{11,20}" title="Nomor handphone harus berupa angka dengan minimal 11 digit" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    </div>
                    <br>
                    <div>
                        <label>Full Address</label><br>
                        <textarea name="alamat" rows="4" required>{{ old('alamat') }}</textarea>
                    </div>

                    <h3>COURIER</h3>
                    <div>
                        <select name="kurir" required>
                            <option value="">Select Courier</option>
                            @foreach($courierOptions as $name => $data)
                                <option value="{{ $name }}" {{ old('kurir') === $name ? 'selected' : '' }}>
                                    {{ $name }} - Rp {{ number_format($data['ongkir'], 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td width="50%" valign="top">
                    <h3>TRANSACTION SUMMARY</h3>
                    <ul>
                        @foreach($cart->items as $item)
                        <li>
                            {{ $item->variant->product->nama_product }} ({{ $item->variant->size->nama_size }}) 
                            x {{ $item->qty }} = Rp {{ number_format($item->qty * $item->variant->product->harga, 0, ',', '.') }}
                        </li>
                        @endforeach
                    </ul>
                    
                    <hr>
                    <p>Total Item Price: Rp {{ number_format($totalItemPrice, 0, ',', '.') }}</p>
                    <p>Shipping Fee: (Pilih Kurir)</p>
                    <p>Shipping Insurance: (Pilih Kurir)</p>
                    <p><strong>TOTAL COST: Menyesuaikan Kurir</strong></p>

                    <h3>PAYMENT METHOD</h3>
                    <div>
                        <select name="metode_pembayaran" required>
                            <option value="">Select Payment Method</option>
                            @foreach($bankAccounts as $method)
                                <option value="{{ $method }}" {{ old('metode_pembayaran') === $method ? 'selected' : '' }}>
                                    {{ $method }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <br><br>
                    <button type="submit" {{ $hasOutOfStock ? 'disabled' : '' }}>PAY NOW</button>
                </td>
            </tr>
        </table>
    </form>
    
    <br>
    <a href="{{ route('cart.index') }}">Kembali ke Keranjang</a>
</body>
</html>
