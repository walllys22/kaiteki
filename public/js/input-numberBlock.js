// Bloquear la rueda del ratón para TODOS los inputs de tipo número
document.addEventListener('DOMContentLoaded', function() {
// Seleccionar todos los inputs de tipo número
    const numberInputs = document.querySelectorAll('input[type="number"]');

    // Para cada input, agregar el event listener para bloquear la rueda
    numberInputs.forEach(input => {
        input.addEventListener('wheel', function(e) {
            // Prevenir el comportamiento por defecto
            e.preventDefault();
        });
    });
});