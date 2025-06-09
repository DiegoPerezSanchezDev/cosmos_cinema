// Código JavaScript para elementos interactivos de la página.
// Asegúrate de que este código se carga DESPUÉS de que los elementos HTML existan (ej: al final del body o dentro de DOMContentLoaded).

document.addEventListener('DOMContentLoaded', () => {

        const mostrarMenusBtn = document.getElementById('mostrarMenus');
        const seccionMenus = document.getElementById('seccionMenus');
        const cerrarMenusBtn = document.getElementById('cerrarMenus');
        const seccionCompra = document.getElementById('seccionCompra');
    

        if (mostrarMenusBtn && seccionMenus && seccionCompra) {
            mostrarMenusBtn.addEventListener('click', () => {
                
                if (seccionMenus.classList.contains('hidden')) {
                    seccionMenus.classList.remove('hidden');
                    seccionMenus.classList.add('visible');
                }
                
                if (seccionCompra.classList.contains('visible')) {
                    seccionCompra.classList.remove('visible');
                    seccionCompra.classList.add('hidden');
                }
            });
        }
    
    
        


    const menuItems = document.querySelectorAll('.menu-item');

    if (menuItems.length > 0) {
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                item.classList.toggle('is-flipped');
            });
        });
    }

    if (cerrarMenusBtn && seccionMenus) {
        
        cerrarMenusBtn.addEventListener('click', () => {
            
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                
                setTimeout(() => {
                    seccionMenus.classList.add('hidden');
                }, 500);
        });
    }

});