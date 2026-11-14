<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'payment', 'items.variant.product', 'items.variant.size']);

        if ($request->filled('status')) {
            $query->where('status_pesanan', $request->status);
        }

        // Cari berdasarkan nomor pesanan (#TRX-0012 / 12), nama akun, atau nama penerima
        if ($request->filled('q')) {
            $q = trim($request->q);
            $id = (int) preg_replace('/\D/', '', $q);

            $query->where(function ($w) use ($q, $id) {
                if ($id > 0) {
                    $w->orWhere('id_order', $id);
                }
                $w->orWhere('alamat_pengiriman', 'like', "%{$q}%")
                  ->orWhereHas('user', fn ($u) => $u->where('nama', 'like', "%{$q}%"));
            });
        }

        $sortId = $request->get('sort_id', 'desc');
        if (in_array($sortId, ['asc', 'desc'])) {
            $query->orderBy('id_order', $sortId);
        } else {
            $query->orderBy('tanggal_order', 'desc');
        }

        $orders = $query->paginate(20)->withQueryString();

        $statusCounts = Order::selectRaw('status_pesanan, COUNT(*) as total')
            ->groupBy('status_pesanan')
            ->pluck('total', 'status_pesanan');

        return view('admin.orders.index', [
            'orders'       => $orders,
            'statusLabels' => Order::STATUS_LABELS,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_pesanan' => ['required', Rule::in(array_keys(Order::STATUS_LABELS))],
        ]);

        $order = Order::findOrFail($id);

        $order->update([
            'status_pesanan' => $request->status_pesanan,
        ]);

        $code = '#TRX-'.str_pad($order->id_order, 4, '0', STR_PAD_LEFT);

        return redirect()->back()
                         ->with('success', "Status {$code} diubah menjadi \"{$order->statusLabel()}\".");
    }
}
