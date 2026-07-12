<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login — {{ config('app.name', 'Ehlom OS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <canvas id="auth-bg"></canvas>

    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="auth-logo-mark">E</div>
                <div>
                    <div class="auth-logo-text">EHLOM OS</div>
                    <div class="auth-logo-sub">Client Dashboard</div>
                </div>
            </div>

            <div class="auth-head">
                <div class="auth-title">Welcome back</div>
                <div class="auth-sub">Sign in to your client dashboard</div>
            </div>

            @if ($errors->any())
                <div class="eos-alert-bar warn" style="margin-bottom:14px;">
                    <i class="ti ti-alert-triangle"></i> {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.login') }}">
                @csrf
                <div class="eos-field">
                    <label class="eos-label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="eos-input" required autofocus autocomplete="username">
                </div>
                <div class="eos-field">
                    <label class="eos-label" for="password">Password</label>
                    <input id="password" type="password" name="password"
                           class="eos-input" required autocomplete="current-password">
                </div>
                <label class="auth-remember">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <button type="submit" class="eos-btn eos-btn-primary auth-submit">
                    <i class="ti ti-login-2"></i> Log In
                </button>
                <a href="{{ route('tenant.register') }}" class="auth-link">No account? Register</a>
            </form>
        </div>
        <div class="auth-foot">Ehlom Digital &middot; Client Portal</div>
    </div>

    <script>
    (function(){const c=document.getElementById('auth-bg'),ctx=c.getContext('2d');let particles=[],w,h,mouse={x:null,y:null,radius:120};function resize(){w=c.width=window.innerWidth;h=c.height=window.innerHeight}window.addEventListener('resize',resize);resize();class Particle{constructor(){this.reset()}reset(){this.x=Math.random()*w;this.y=Math.random()*h;this.size=Math.random()*2+0.5;this.speedX=Math.random()*0.4-0.2;this.speedY=Math.random()*0.4-0.2;this.opacity=Math.random()*0.4+0.1}update(){this.x+=this.speedX;this.y+=this.speedY;if(this.x<0||this.x>w||this.y<0||this.y>h)this.reset();const dx=mouse.x-this.x,dy=mouse.y-this.y,dist=Math.sqrt(dx*dx+dy*dy);if(dist<mouse.radius){const force=(mouse.radius-dist)/mouse.radius;this.x-=dx*force*0.02;this.y-=dy*force*0.02}}}for(let i=0;i<280;i++)particles.push(new Particle);function animate(){ctx.clearRect(0,0,w,h);particles.forEach(p=>{p.update();ctx.beginPath();ctx.arc(p.x,p.y,p.size,0,Math.PI*2);ctx.fillStyle='rgba(79,142,247,'+p.opacity+')';ctx.fill();});particles.forEach((a,i)=>{for(let j=i+1;j<particles.length;j++){const b=particles[j],dx=a.x-b.x,dy=a.y-b.y,dist=Math.sqrt(dx*dx+dy*dy);if(dist<120){ctx.beginPath();ctx.moveTo(a.x,a.y);ctx.lineTo(b.x,b.y);ctx.strokeStyle='rgba(79,142,247,'+(0.08*(1-dist/120))+')';ctx.lineWidth=0.5;ctx.stroke()}}});requestAnimationFrame(animate)}animate();c.addEventListener('mousemove',e=>{mouse.x=e.clientX;mouse.y=e.clientY});c.addEventListener('mouseleave',()=>{mouse.x=null;mouse.y=null});c.addEventListener('click',e=>{for(let i=0;i<20;i++){const p=new Particle;p.x=e.clientX;p.y=e.clientY;p.speedX=(Math.random()-0.5)*3;p.speedY=(Math.random()-0.5)*3;particles.push(p)}setTimeout(()=>particles.splice(0,20),2000)})})();
    </script>
</body>
</html>
