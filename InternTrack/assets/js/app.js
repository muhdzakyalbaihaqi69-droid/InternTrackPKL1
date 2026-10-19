// Konfirmasi hapus: <form data-confirm="Pesan ...">
document.addEventListener('submit', function (ev) {
    var f = ev.target;

    // Validasi sisi klien untuk field "required" (selain validasi server)
    if (f.hasAttribute('data-validate')) {
        var ok = true;
        f.querySelectorAll('[required]').forEach(function (el) {
            var kosong = el.value.trim() === '';
            el.classList.toggle('invalid', kosong);
            if (kosong) { ok = false; }
        });
        if (!ok) { ev.preventDefault(); alert('Lengkapi semua kolom yang wajib diisi (*).'); return; }
    }

    var pesan = f.getAttribute('data-confirm');
    if (pesan && !confirm(pesan)) { ev.preventDefault(); }
});

// Sembunyikan notifikasi otomatis
document.querySelectorAll('.alert-success, .alert-info').forEach(function (a) {
    setTimeout(function () { a.style.display = 'none'; }, 5000);
});
