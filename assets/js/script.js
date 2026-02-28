(function () {
    const MAX_UPLOAD_BYTES = 2 * 1024 * 1024;
    const TARGET_UPLOAD_BYTES = 1200 * 1024;

    const loader = document.getElementById('loader');
    const backToTop = document.getElementById('backToTop');
    let imageProcessing = false;

    window.addEventListener('load', function () {
        if (loader) {
            loader.classList.add('hidden');
            setTimeout(function () {
                loader.remove();
            }, 500);
        }
    });

    const revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealEls.length > 0) {
        const observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            },
            { threshold: 0.15 }
        );
        revealEls.forEach(function (el) {
            observer.observe(el);
        });
    } else {
        revealEls.forEach(function (el) {
            el.classList.add('active');
        });
    }

    window.addEventListener('scroll', function () {
        if (!backToTop) return;
        if (window.scrollY > 350) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });

    if (backToTop) {
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const copyBtn = document.getElementById('copyLinkBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', async function () {
            const url = copyBtn.getAttribute('data-url') || window.location.href;
            try {
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    await navigator.clipboard.writeText(url);
                } else {
                    const tempInput = document.createElement('input');
                    tempInput.value = url;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    tempInput.remove();
                }
                copyBtn.textContent = 'Copied';
                setTimeout(function () {
                    copyBtn.textContent = 'Copy Link';
                }, 1800);
            } catch (err) {
                alert('Unable to copy link.');
            }
        });
    }

    const downloadBtn = document.getElementById('downloadCardBtn');
    const wishCard = document.getElementById('wishCard');
    if (downloadBtn && wishCard) {
        downloadBtn.addEventListener('click', function () {
            if (typeof html2canvas !== 'function') {
                alert('Download library failed to load.');
                return;
            }

            html2canvas(wishCard, { backgroundColor: null, scale: 2 }).then(function (canvas) {
                const link = document.createElement('a');
                link.download = downloadBtn.getAttribute('data-download-name') || 'wish-card.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        });
    }

    const fireworksCanvas = document.getElementById('fireworksCanvas');
    if (fireworksCanvas) {
        runFireworks(fireworksCanvas);
    }

    const uploadInput = document.getElementById('user_image');
    const uploadPreview = document.getElementById('uploadPreview');
    const posterWishForm = document.getElementById('posterWishForm');
    const posterSubmitBtn = document.getElementById('posterSubmitBtn');

    if (uploadInput && uploadPreview) {
        uploadPreview.textContent = 'Your photo preview will appear here';

        uploadInput.addEventListener('change', async function (event) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) {
                uploadPreview.textContent = 'Your photo preview will appear here';
                return;
            }

            if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
                uploadPreview.textContent = 'Invalid file type.';
                uploadInput.value = '';
                return;
            }

            imageProcessing = true;
            if (posterSubmitBtn) {
                posterSubmitBtn.disabled = true;
                posterSubmitBtn.textContent = 'Optimizing Image...';
            }

            let finalFile = file;
            if (file.size > TARGET_UPLOAD_BYTES) {
                try {
                    finalFile = await compressImage(file, TARGET_UPLOAD_BYTES, 1280);
                } catch (e) {
                    finalFile = file;
                }
            }

            if (finalFile.size > MAX_UPLOAD_BYTES) {
                uploadPreview.textContent = 'Image too large. Please use image under 2MB.';
                uploadInput.value = '';
                imageProcessing = false;
                if (posterSubmitBtn) {
                    posterSubmitBtn.disabled = false;
                    posterSubmitBtn.textContent = 'Generate Poster Wish';
                }
                return;
            }

            if (finalFile !== file && typeof DataTransfer !== 'undefined') {
                const dt = new DataTransfer();
                dt.items.add(finalFile);
                uploadInput.files = dt.files;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Upload preview';
                uploadPreview.innerHTML = '';
                uploadPreview.appendChild(img);
            };
            reader.readAsDataURL(finalFile);

            imageProcessing = false;
            if (posterSubmitBtn) {
                posterSubmitBtn.disabled = false;
                posterSubmitBtn.textContent = 'Generate Poster Wish';
            }
        });
    }

    if (posterWishForm) {
        posterWishForm.addEventListener('submit', function (event) {
            const selectedFile = uploadInput && uploadInput.files && uploadInput.files[0] ? uploadInput.files[0] : null;
            if (!selectedFile) {
                return;
            }

            if (imageProcessing) {
                event.preventDefault();
                alert('Image still processing. Please wait 1-2 seconds and submit again.');
                return;
            }

            if (selectedFile.size > MAX_UPLOAD_BYTES) {
                event.preventDefault();
                alert('Image is more than 2MB. Please choose a smaller image.');
                return;
            }

            showSubmitLoader(15);
            if (posterSubmitBtn) {
                posterSubmitBtn.disabled = true;
            }
        });
    }

    function showSubmitLoader(seconds) {
        const wrap = document.createElement('div');
        wrap.className = 'submit-loader';
        wrap.innerHTML = '<div class="submit-loader-card"><div class="spinner"></div><p id="submitLoaderText">Poster ban raha hai... ' + seconds + 's</p></div>';
        document.body.appendChild(wrap);

        const textNode = document.getElementById('submitLoaderText');
        let remaining = seconds;
        const timer = setInterval(function () {
            remaining -= 1;
            if (remaining > 0) {
                if (textNode) {
                    textNode.textContent = 'Poster ban raha hai... ' + remaining + 's';
                }
            } else {
                if (textNode) {
                    textNode.textContent = 'Almost done...';
                }
                clearInterval(timer);
            }
        }, 1000);
    }

    function compressImage(file, maxBytes, maxDim) {
        return new Promise(function (resolve, reject) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    let width = img.width;
                    let height = img.height;

                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        reject(new Error('Canvas unavailable'));
                        return;
                    }
                    ctx.drawImage(img, 0, 0, width, height);

                    let quality = 0.9;
                    function exportBlob() {
                        canvas.toBlob(function (blob) {
                            if (!blob) {
                                reject(new Error('Compression failed'));
                                return;
                            }

                            if (blob.size <= maxBytes || quality <= 0.5) {
                                const compressedName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                                resolve(new File([blob], compressedName, { type: 'image/jpeg' }));
                                return;
                            }

                            quality -= 0.1;
                            exportBlob();
                        }, 'image/jpeg', quality);
                    }
                    exportBlob();
                };
                img.onerror = function () {
                    reject(new Error('Invalid image'));
                };
                img.src = e.target.result;
            };
            reader.onerror = function () {
                reject(new Error('Cannot read image'));
            };
            reader.readAsDataURL(file);
        });
    }

    function runFireworks(canvas) {
        const ctx = canvas.getContext('2d');
        let particles = [];
        let rafId;

        function resize() {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
        }

        function createBurst() {
            const x = Math.random() * canvas.width;
            const y = Math.random() * (canvas.height * 0.7);
            const colors = ['#f59e0b', '#38bdf8', '#f43f5e', '#22c55e', '#fde047'];
            for (let i = 0; i < 40; i++) {
                const angle = (Math.PI * 2 * i) / 40;
                const speed = Math.random() * 3 + 1.5;
                particles.push({
                    x: x,
                    y: y,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    life: 80,
                    color: colors[Math.floor(Math.random() * colors.length)]
                });
            }
        }

        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(function (p) {
                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.02;
                p.life -= 1;
                ctx.beginPath();
                ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.fill();
            });
            particles = particles.filter(function (p) {
                return p.life > 0;
            });
            rafId = requestAnimationFrame(draw);
        }

        resize();
        draw();
        const burstTimer = setInterval(createBurst, 1000);
        window.addEventListener('resize', resize);
        window.addEventListener('beforeunload', function () {
            cancelAnimationFrame(rafId);
            clearInterval(burstTimer);
        });
    }
})();
