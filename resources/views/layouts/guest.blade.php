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

    <canvas id="auth-bg" aria-hidden="true"></canvas>

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
        let W, H;

        function resize() {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const particleCount = window.innerWidth < 600 ? 34 : 58;
        const particles = Array.from({ length: particleCount }, (_, index) => ({
            x: Math.random() * window.innerWidth,
            y: Math.random() * window.innerHeight,
            vx: (Math.random() - .5) * .15,
            vy: (Math.random() - .5) * .15,
            radius: 1.1 + Math.random() * 1.8,
            hue: index % 3 === 0 ? 189 : index % 3 === 1 ? 224 : 271,
        }));

        function drawAmbientGlow(time) {
            const drift = reduced ? 0 : time * .00008;
            const left = ctx.createRadialGradient(W * (.18 + Math.sin(drift) * .05), H * .16, 0, W * .18, H * .16, Math.max(W, H) * .58);
            left.addColorStop(0, 'rgba(25, 164, 208, .18)');
            left.addColorStop(1, 'rgba(25, 164, 208, 0)');
            ctx.fillStyle = left;
            ctx.fillRect(0, 0, W, H);

            const right = ctx.createRadialGradient(W * (.82 + Math.cos(drift * .8) * .04), H * .74, 0, W * .82, H * .74, Math.max(W, H) * .6);
            right.addColorStop(0, 'rgba(125, 79, 220, .17)');
            right.addColorStop(1, 'rgba(125, 79, 220, 0)');
            ctx.fillStyle = right;
            ctx.fillRect(0, 0, W, H);
        }

        function frame() {
            const now = performance.now();
            ctx.fillStyle = '#0b1020';
            ctx.fillRect(0, 0, W, H);
            drawAmbientGlow(now);

            for (const p of particles) {
                if (!reduced) {
                    p.x += p.vx;
                    p.y += p.vy;
                    if (p.x < -20 || p.x > W + 20) p.vx *= -1;
                    if (p.y < -20 || p.y > H + 20) p.vy *= -1;
                }
            }

            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];
                for (let j = i + 1; j < particles.length; j++) {
                    const q = particles[j];
                    const dx = p.x - q.x, dy = p.y - q.y;
                    const distance = Math.hypot(dx, dy);
                    if (distance < 150) {
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y);
                        ctx.lineTo(q.x, q.y);
                        ctx.strokeStyle = `rgba(105, 165, 255, ${.12 * (1 - distance / 150)})`;
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }
                }
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = `hsla(${p.hue}, 90%, 70%, .9)`;
                ctx.shadowBlur = 12;
                ctx.shadowColor = `hsla(${p.hue}, 90%, 62%, .8)`;
                ctx.fill();
            }
            ctx.shadowBlur = 0;
            if (!reduced) requestAnimationFrame(frame);
        }
        frame();
    })();
    </script>
</body>
</html>
