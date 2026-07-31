{{--
    Harus dipasang di dalam <head>, sebelum bagian mana pun dari halaman
    dirender. Script ini sengaja inline dan sinkron: kalau ditunda, halaman
    sempat tampil terang sekejap sebelum berganti gelap.
--}}
<script>
    (function () {
        var stored = null;

        try {
            stored = localStorage.getItem('theme');
        } catch (e) {
            // Mode privat pada beberapa browser melarang akses localStorage.
        }

        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        var isDark = stored === 'dark' || (stored !== 'light' && prefersDark);

        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
