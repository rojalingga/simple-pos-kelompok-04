@extends('layouts.app')

@section('title', 'Kasir')

@section('content')

    <h1 class="text-lg font-semibold mb-4">
        Transaksi Kasir
    </h1>

    <div
        x-data="{
            cart: [],

            addToCart(id, name, price) {
                this.cart.push({
                    id: id,
                    name: name,
                    price: price
                });
            },

            subtotal() {
                return this.cart.reduce(
                    (sum, item) => sum + item.price,
                    0
                );
            },

            removeFromCart(id) {
                this.cart = this.cart.filter(
                    item => item.id !== id
                );
            }
        }"
    >

        <!-- Daftar Produk -->
        <div class="grid grid-cols-3 gap-4">

            @foreach ($products as $product)

                <div
                    class="border rounded-md p-3 cursor-pointer hover:bg-slate-50"
                    @click="addToCart(
                        {{ $product->id }},
                        '{{ $product->name }}',
                        {{ $product->price }}
                    )"
                >

                    <div class="flex items-center justify-between gap-2">

                        <p class="font-medium">
                            {{ $product->name }}
                        </p>

                        @if ($product->stock < 10)
                            <span
                                class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-full whitespace-nowrap"
                            >
                                Stok Menipis
                            </span>
                        @endif

                    </div>

                    <!-- Harga -->
                    <p class="text-sm text-slate-500 mt-1">
                        Rp {{ number_format($product->price) }}
                    </p>

                    <!-- Stok -->
                    <p class="text-sm text-slate-500 mt-1">
                        Stok: {{ $product->stock }}
                    </p>

                </div>

            @endforeach

        </div>

        <!-- Keranjang -->
        <div class="mt-4 border-t pt-3">

            <template x-for="item in cart" :key="item.id">

                <div class="flex items-center gap-2 mb-2">

                    <p x-text="item.name + ' - Rp ' + item.price"></p>

                    <button
                        type="button"
                        class="text-sm text-red-600 hover:text-red-800"
                        @click="removeFromCart(item.id)"
                    >
                        Hapus
                    </button>

                </div>

            </template>

            <!-- Subtotal -->
            <p class="font-semibold mt-2">
                Subtotal: Rp
                <span x-text="subtotal()"></span>
            </p>

        </div>

    </div>

@endsection