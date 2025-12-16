/**
 * JavaScript - CLIENTE
 * 
 * Este código se ejecuta en el navegador del CLIENTE
 * para mejorar la experiencia del usuario (UX).
 * 
 * Nota: La validación principal se hace en el SERVIDOR.
 * Este código solo mejora la experiencia del usuario.
 */

// CLIENTE: Mejora la experiencia al seleccionar tipo de membresía
document.addEventListener('DOMContentLoaded', function() {
    const membershipTypeSelect = document.getElementById('membership_type_id');
    const amountInput = document.getElementById('amount');
    
    // Si estamos en la página de crear pago
    if (membershipTypeSelect && amountInput) {
        // CLIENTE: Actualiza automáticamente el monto cuando se selecciona un tipo
        membershipTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            
            if (price && !amountInput.value) {
                amountInput.value = parseFloat(price).toFixed(2);
            }
        });
    }
    
    // CLIENTE: Auto-oculta mensajes de alerta después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // CLIENTE: Validación básica de formularios antes de enviar al SERVIDOR
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            // CLIENTE: Previene envío si hay errores (mejora UX)
            // Nota: El SERVIDOR también valida por seguridad
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, complete todos los campos requeridos');
            }
        });
    });
});

