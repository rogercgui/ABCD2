const desenharImagem = (canvas) => {
    const src = canvas.dataset.src;
    if (!src) return;

    const ctx = canvas.getContext('2d');
    const img = new Image();
    img.crossOrigin = "anonymous";
    
    img.onerror = () => console.error("Erro ao carregar:", src);

    img.onload = () => {
        // Define a resolução interna limite (1920px) para não travar a RAM do navegador
        const MAX_WIDTH = 1920; 
        const scale = Math.min(1, MAX_WIDTH / img.width);
        
        const internalWidth = img.width * scale;
        const internalHeight = img.height * scale;

        // O SEGREDO: Dá ao canvas a mesma resolução real da imagem
        canvas.width = internalWidth;
        canvas.height = internalHeight;

        // Desenha a imagem na resolução alta
        ctx.drawImage(img, 0, 0, internalWidth, internalHeight);

        // Desenha a marca d'água proporcional a essa resolução
        const fontSize = Math.floor(internalWidth / 15);
        ctx.font = `bold ${fontSize}px Arial`;
        ctx.fillStyle = "rgba(255, 255, 255, 0.3)";
        ctx.textAlign = "center";
        ctx.save();
        ctx.translate(internalWidth / 2, internalHeight / 2);
        ctx.rotate(-Math.PI / 4);
        ctx.fillText("ACERVO PROTEGIDO", 0, 0);
        ctx.restore();

        canvas.setAttribute('data-processed', 'true');
    };

    img.src = src;

    // Bloqueia clique direito
    canvas.addEventListener("contextmenu", e => e.preventDefault());

    // Configura o botão de Fullscreen
    const btn = canvas.parentElement.querySelector('.btn-fullscreen-canvas') || 
                canvas.closest('div').querySelector('.btn-fullscreen-canvas');
                
    if (btn && !btn.hasAttribute('data-click-bound')) {
        btn.addEventListener('click', () => {
            if (canvas.requestFullscreen) canvas.requestFullscreen();
            else if (canvas.webkitRequestFullscreen) canvas.webkitRequestFullscreen();
        });
        btn.setAttribute('data-click-bound', 'true'); // Evita duplicar cliques
    }
};

// Observador de Visibilidade (Lazy Load para imagens do Looping e Modal)
const visibilidadeObserver = new IntersectionObserver((entradas, obs) => {
    entradas.forEach(entrada => {
        if (entrada.isIntersecting) {
            desenharImagem(entrada.target);
            obs.unobserve(entrada.target); // Para de vigiar depois que desenhou
        }
    });
}, { threshold: 0.01 }); 

// Busca os canvas novos na tela
const procurarCanvas = () => {
    document.querySelectorAll('.protected-canvas:not([data-observing="true"])').forEach(canvas => {
        canvas.setAttribute('data-observing', 'true');
        visibilidadeObserver.observe(canvas);
    });
};

// Inicia a vigilância
const iniciar = () => {
    if (!document.body) { setTimeout(iniciar, 10); return; }
    procurarCanvas();
    new MutationObserver(procurarCanvas).observe(document.body, { childList: true, subtree: true });
};

iniciar();