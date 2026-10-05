Apakah Validasi Numeric pada Total Cukup Mencegah Manipulasi?
Tidak. Aturan numeric hanya mengecek apakah nilainya berupa angka, bukan apakah angkanya benar. Total 1000 untuk belanja 1.000.000 akan tetap lolos karena 1000 memang angka yang valid.
Selain itu, form berjalan di browser sehingga sepenuhnya ada di sisi pengguna dan gampang diubah: lewat inspect element, mematikan JavaScript, atau mengirim request langsung dengan curl. Jadi angka kiriman client tidak bisa dipercaya untuk hal sepenting total bayar.
Solusinya seperti yang dipakai di project ini: TransactionController@store mengabaikan total dari form dan menghitung ulang sendiri dari harga di database (subtotal = harga x qty, lalu dijumlahkan). Dengan begitu tidak ada celah memasukkan angka dari luar.
Aturan numeric sendiri tetap berguna, tapi fungsinya hanya supaya input aneh ditolak dengan pesan error yang rapi, bukan sebagai pengaman. Keamanannya dijamin oleh perhitungan ulang di server.
