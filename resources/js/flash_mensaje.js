// Lógica de mostrar el mensaje de resultado

document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = document.getElementById('flash-message');
    if (flashMessage && flashMessage.textContent.trim() !== '') {
        flashMessage.classList.add('show');
        setTimeout(function() {
            flashMessage.classList.remove('show');
            setTimeout(() => {
                flashMessage.textContent = '';
            }, 500);
        }, 3000);
    }
});

/* document.addEventListener('DOMContentLoaded', function() {
    const flashMessage = document.getElementById('flash-message');
    if (flashMessage) {
        if (window.innerWidth <= 768) {
            flashMessage.style.setProperty('display', 'none', 'important');
        }
    }
});

window.addEventListener('resize', function() {
    const flashMessage = document.getElementById('flash-message');
    if (flashMessage) {
        if (window.innerWidth <= 768) {
            flashMessage.style.setProperty('display', 'none', 'important');
        } else {
            flashMessage.style.removeProperty('display'); // O podrías establecerlo a 'block' o el valor por defecto que tenga
        }
    }
}); */