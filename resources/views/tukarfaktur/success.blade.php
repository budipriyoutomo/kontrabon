<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tukar Faktur Berhasil</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(180deg, #f8f9fa, #eef1f4);
    }

    .card {
        border: none;
        border-radius: 1rem;
        animation: fadeUp 0.6s ease-out both;
    }

    .icon-success {
        width: 64px;
        height: 64px;
        background: #e8f5ee;
        color: #198754;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto;
        animation: pop 0.5s ease-out 0.3s both;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pop {
        0% { transform: scale(0.6); opacity: 0; }
        60% { transform: scale(1.15); }
        80% { transform: scale(0.95); }
        100% { transform: scale(1); opacity: 1; }
    }
    
    @media (prefers-reduced-motion: reduce) {
        .card,
        .icon-success {
            animation: none;
        }
    }

</style>

</head>

<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">

            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5 text-center">

                    <div class="icon-success mb-3">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    <h4 class="fw-bold mb-2">Tukar Faktur Terkirim</h4>
                    <p class="text-muted mb-4">
                        Terima kasih. Data tukar faktur berhasil dikirim dan akan diproses oleh tim finance.
                    </p>

                    <div class="d-grid gap-2">
                        <a href="/kontrabon" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Ajukan Tukar Faktur Lagi
                        </a>
                    </div>

                </div>
            </div>

            <p class="text-center text-muted small mt-4">
                © {{ date('Y') }} Maharasa Group
            </p>

        </div>
    </div>
</div>

</body>
</html>
