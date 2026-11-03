@extends('layouts.app')

@section('title', 'Import Produk')

@section('content')
<h1 class="text-lg font-semibold mb-4">Import Produk dari CSV</h1>

@if (session('success'))
    <div class="bg-green-50 text-green-700 p-3 rounded-md mb-4">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="max-w-md">
    @csrf
    <label class="block mb-3">
        <span class="text-sm font-medium">File CSV</span>
        <input type="file" name="csv" accept=".csv" class="mt-1 block w-full text-sm">
        @error('csv')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </label>
    <label class="block mb-3">
        <span class="text-sm font-medium">Metode Import</span>
        <select name="mode" class="mt-1 block w-full border rounded-md px-3 py-2 text-sm">
            <option value="manual" @selected(old('mode', 'manual') === 'manual')>
                Manual (parsing CSV per baris)
            </option>
            <option value="excel" @selected(old('mode') === 'excel')>
                Laravel Excel (ToModel + HeadingRow + Chunk)
            </option>
        </select>
        @error('mode')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </label>
    <p class="text-xs text-slate-500 mb-3">
        <strong>Manual</strong> memberi kontrol penuh per baris. <strong>Laravel Excel</strong>
        lebih ringkas dan membaca data per chunk, cocok untuk file besar.
    </p>
    <p class="text-xs text-slate-500 mb-3">Kolom yang dibutuhkan: <code>name</code>, <code>category_id</code>, <code>price</code>.</p>
    <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm">Import</button>
</form>

<div class="mt-8">
    <div class="mb-3 flex items-baseline justify-between gap-4">
        <h2 class="text-base font-semibold">Baris Gagal Import</h2>
        <span class="text-sm text-slate-500">{{ $importErrors->total() }} baris gagal</span>
    </div>

    @if ($importErrors->isEmpty())
        <div class="rounded-md border bg-white p-4 text-sm text-slate-500">
            Belum ada baris yang gagal di-import.
        </div>
    @else
        <div class="overflow-x-auto rounded-md border bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-2 font-medium">File</th>
                        <th class="px-4 py-2 font-medium">Baris</th>
                        <th class="px-4 py-2 font-medium">Pesan Error</th>
                        <th class="px-4 py-2 font-medium">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($importErrors as $error)
                        <tr class="border-b last:border-b-0 align-top">
                            <td class="px-4 py-2">{{ $error->file_name ?? '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">Baris {{ $error->row_number }}</td>
                            <td class="px-4 py-2 text-red-700">{{ $error->message }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-slate-500">
                                {{ $error->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $importErrors->links() }}
        </div>
    @endif
</div>
@endsection
