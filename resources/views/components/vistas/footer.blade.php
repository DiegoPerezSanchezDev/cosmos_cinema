<div class="footer-content">
    <div class="footer-section links">
    <h4>Información</h4>
    <div class="links-columns">
        <ul class="link-column">
            <li><a href="{{ route('footer_politica_privacidad') }}">Política de Privacidad</a></li>
            <li><a href="{{ route('footer_terminos_y_condiciones') }}">Términos y Condiciones</a></li>
            <li><a href="{{ route('footer_aviso_legal') }}">Aviso Legal</a></li>
        </ul>
        <ul class="link-column">
            <li><a href="{{ route('footer_politica_de_cookies') }}">Política de Cookies</a></li>
            <li><a href="{{ route('footer_preguntas_frecuentes') }}">Preguntas Frecuentes (FAQ)</a></li>
            <li><a href="{{ route('footer_contacto') }}">Contacto</a></li>
        </ul>
    </div>
</div>

<div class="footer-section social">
    <h4>Síguenos</h4>
        <div class="social-icons">
            <a href="https://instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
            </a>
            <a href="https://facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
            </a>
            <a href="https://twitter.com/" target="_blank" rel="noopener noreferrer" aria-label="Twitter / X">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://tiktok.com/@tu_cine" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-1.06-.6-1.9-1.44-2.46-2.45a4.96 4.96 0 0 1-1.31-3.62c.01-2.92-.01-5.84.02-8.75.08-1.4.54-2.79 1.35-3.94 1.31-1.92 3.58 3.17 5.91 3.21.82.03 1.64-.16 2.42-.51.78-.35 1.44-.86 1.98-1.48.01-2.92-.01-5.84.02-8.75z"/></svg>
            </a>
            <a href="https://snapchat.com/" target="_blank" rel="noopener noreferrer" aria-label="Snapchat">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C6.45 0 2.02 4.429 2.02 9.938c0 3.071 1.41 5.807 3.639 7.569L2 24l6.62-3.625c.96.256 1.971.395 3.009.395 5.488 0 9.97-4.43 9.97-9.938C21.999 4.429 17.519 0 12 0zm.062 18.292c-1.019 0-2.006-.147-2.918-.421l-.21-.075-2.142 1.172.99-2.062-.149-.242c-2.018-1.65-3.27-4.091-3.27-6.726C4.363 5.532 7.829 2.04 12 2.04s7.637 3.492 7.637 7.852c0 4.359-3.466 7.9-7.575 7.9zM8.08 8.219c0-.552.448-1 .999-1s.999.448.999 1v3.802c0 .552-.448 1-.999 1s-.999-.448-.999-1V8.22zm6.841 0c0-.552.448-1 .999-1s.999.448.999 1v3.802c0 .552-.448 1-.999 1s-.999-.448-.999-1V8.22z"/></svg>
            </a>
        </div>
    </div>
</div>

<div class="footer-bottom">
    <p>© <span id="currentYear"></span> Cosmos Cinema S.L. Todos los derechos reservados.</p>
    <p class="made-with-love">Hecho con <span class="heart">♥</span> para los amantes del cine</p>
</div>

<script>
    document.getElementById('currentYear').textContent = new Date().getFullYear();
</script>