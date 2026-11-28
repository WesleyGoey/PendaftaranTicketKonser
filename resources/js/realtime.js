// Padanan bagian EventSource di public/script.js repo kelas: setiap ada perubahan,
// data dimuat ulang (di sana fetchStudents()). Di sini halaman yang sama diambil lagi,
// lalu hanya bagian bertanda data-live yang diganti.
// Browser hanya bicara dengan website ini; key Supabase tidak pernah dikirim ke sini.
if (document.querySelector('[data-live]') && 'EventSource' in window) {
    let version = null;
    let refreshing = false;
    let again = false;

    const formConcert = (page) =>
        page.querySelector('#booking-form input[name="concert_id"]')?.value;

    async function refresh() {
        if (refreshing) {
            again = true;
            return;
        }
        refreshing = true;

        try {
            const response = await fetch(window.location.pathname, {
                headers: { Accept: 'text/html' },
                cache: 'no-store',
            });

            // Sesi habis atau data hilang: muat halaman sepenuhnya.
            if (response.redirected || response.status === 404) {
                window.location.reload();
                return;
            }
            if (!response.ok) {
                return;
            }

            const fresh = new DOMParser().parseFromString(
                await response.text(),
                'text/html',
            );

            // Form pemesanan muncul, hilang, atau ganti konser (misalnya habis).
            if (formConcert(fresh) !== formConcert(document)) {
                window.location.reload();
                return;
            }

            // Form tidak diganti, supaya pilihan kursi yang sedang diisi tidak hilang.
            document.querySelectorAll('[data-live]').forEach((element) => {
                const next = fresh.querySelector(
                    `[data-live="${element.dataset.live}"]`,
                );
                if (next && next.innerHTML !== element.innerHTML) {
                    element.innerHTML = next.innerHTML;
                }
            });
        } catch (error) {
            console.warn('Realtime refresh failed:', error);
        } finally {
            refreshing = false;
            if (again) {
                again = false;
                refresh();
            }
        }
    }

    function updateStock(concerts) {
        const id = formConcert(document);
        const available = concerts?.[id];

        if (Number.isInteger(available) && available >= 0) {
            document
                .querySelectorAll('[data-tickets-left]')
                .forEach((element) => (element.textContent = available));
            document.dispatchEvent(
                new CustomEvent('concert:stock', { detail: { available } }),
            );
        }
    }

    const events = new EventSource('/events');

    events.onmessage = (event) => {
        let payload;

        try {
            payload = JSON.parse(event.data);
        } catch {
            return;
        }

        // Koneksi disambung ulang berkala; abaikan data yang sama. Kecuali di My
        // Tickets: pesanan yang berubah saat menyambung ulang tidak mengubah versi.
        const checkTickets =
            payload.snapshot &&
            document.querySelector('[data-live="tickets"]');

        if (payload.version === version && !checkTickets) {
            return;
        }
        version = payload.version;

        updateStock(payload.concerts);
        refresh();
    };

    // Tutup koneksi saat pindah halaman supaya proses server cepat bebas.
    window.addEventListener('pagehide', () => events.close());
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            window.location.reload();
        }
    });
}
