# Pengukuran Memori: get() vs lazyById()

Dataset: sekitar 52.500 row pada tabel transactions

| Cara baca data | Peak memory (MB) |
| Transaction::lazyById() | 38.0 MB |
| Transaction::get() | 96.0 MB |


Kesimpulan kelompok: 
Metode `lazyById()` jauh lebih efisien dalam penggunaan memori dibandingkan `get()` karena `lazyById()` mengambil data secara bertahap (chunking), sehingga puncak penggunaan memori (peak memory) tetap rendah dan stabil. Sebaliknya, `get()` memuat seluruh data ke dalam memori server sekaligus, yang berisiko menyebabkan crash akibat melampaui batas memori (`memory_limit`) pada dataset yang sangat besar.