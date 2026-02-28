(function () {
    function launchConfetti(durationMs) {
        const canvas = document.createElement('canvas');
        canvas.className = 'confetti-canvas';
        document.body.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        const particles = [];
        const colors = ['#f43f5e', '#f59e0b', '#22c55e', '#38bdf8', '#eab308', '#ec4899'];
        let rafId;

        function resize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        function seedParticles() {
            for (let i = 0; i < 180; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height - canvas.height,
                    size: Math.random() * 6 + 4,
                    speedY: Math.random() * 3 + 2,
                    speedX: Math.random() * 2 - 1,
                    rotation: Math.random() * 360,
                    color: colors[Math.floor(Math.random() * colors.length)]
                });
            }
        }

        function render() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(function (p) {
                p.y += p.speedY;
                p.x += p.speedX;
                p.rotation += 6;
                if (p.y > canvas.height + 20) {
                    p.y = -20;
                }

                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate((p.rotation * Math.PI) / 180);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                ctx.restore();
            });

            rafId = requestAnimationFrame(render);
        }

        resize();
        seedParticles();
        render();

        setTimeout(function () {
            cancelAnimationFrame(rafId);
            canvas.remove();
        }, durationMs);

        window.addEventListener('resize', resize);
    }

    window.addEventListener('load', function () {
        launchConfetti(5000);
    });
})();
