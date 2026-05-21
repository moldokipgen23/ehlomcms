<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ehlom OS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    @php $authLogo = \App\Models\Setting::imageData('company_logo'); @endphp

    <canvas id="auth-bg"></canvas>

    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-brand">
                @if ($authLogo)
                    <img src="{{ $authLogo }}" alt="Logo" class="auth-logo-img">
                @else
                    <div class="auth-logo-mark">E</div>
                    <div>
                        <div class="auth-logo-text">EHLOM OS</div>
                        <div class="auth-logo-sub">Internal CMS</div>
                    </div>
                @endif
            </div>

            {{ $slot }}
        </div>
        <div class="auth-foot">Ehlom Digital &middot; Admin Console</div>
    </div>

    <script>
    (function () {
        const canvas = document.getElementById('auth-bg');
        const ctx = canvas.getContext('2d');
        let W, H, cx, cy;

        function resize() {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
            cx = W / 2; cy = H / 2;
        }
        resize();
        window.addEventListener('resize', resize);

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const N = window.innerWidth < 600 ? 200 : 380;
        const particles = [];
        for (let i = 0; i < N; i++) {
            const t = i / N;
            particles.push({
                baseA: t * Math.PI * 13,
                baseR: t,
                x: 0, y: 0,
                hue: (t * 290 + 195) % 360,
                size: 1 + t * 2.6,
            });
        }

        const mouse = { x: -9999, y: -9999 };
        window.addEventListener('pointermove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
        window.addEventListener('pointerleave', () => { mouse.x = -9999; mouse.y = -9999; });

        const rings = [];
        window.addEventListener('pointerdown', e => {
            rings.push({ x: e.clientX, y: e.clientY, r: 0, life: 1 });
        });

        let rot = 0;
        function frame() {
            rot += reduced ? 0 : 0.0017;

            ctx.fillStyle = 'rgba(13,15,20,0.30)';
            ctx.fillRect(0, 0, W, H);

            const spiralR = Math.min(W, H) * 0.62;

            for (let i = rings.length - 1; i >= 0; i--) {
                const r = rings[i];
                r.r += 9; r.life -= 0.018;
                if (r.life <= 0) rings.splice(i, 1);
            }

            for (const p of particles) {
                const a = p.baseA + rot;
                let tx = cx + Math.cos(a) * p.baseR * spiralR;
                let ty = cy + Math.sin(a) * p.baseR * spiralR;

                let dx = tx - mouse.x, dy = ty - mouse.y;
                let d2 = dx * dx + dy * dy;
                if (d2 < 20000) {
                    const d = Math.sqrt(d2) || 1;
                    const f = (1 - d / 141) * 64;
                    tx += dx / d * f; ty += dy / d * f;
                }

                for (const r of rings) {
                    let rx = tx - r.x, ry = ty - r.y;
                    const rd = Math.sqrt(rx * rx + ry * ry) || 1;
                    if (Math.abs(rd - r.r) < 64) {
                        const f = (1 - Math.abs(rd - r.r) / 64) * 56 * r.life;
                        tx += rx / rd * f; ty += ry / rd * f;
                    }
                }

                p.x += (tx - p.x) * 0.12;
                p.y += (ty - p.y) * 0.12;

                const hue = p.hue + rot * 40;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fillStyle = `hsla(${hue},85%,63%,0.92)`;
                ctx.shadowBlur = 14;
                ctx.shadowColor = `hsla(${hue},90%,60%,0.85)`;
                ctx.fill();
            }
            ctx.shadowBlur = 0;

            for (const r of rings) {
                ctx.beginPath();
                ctx.arc(r.x, r.y, r.r, 0, Math.PI * 2);
                ctx.strokeStyle = `hsla(258,92%,72%,${r.life * 0.55})`;
                ctx.lineWidth = 2.5;
                ctx.stroke();
            }

            requestAnimationFrame(frame);
        }
        frame();
    })();
    </script>
</body>
</html>
