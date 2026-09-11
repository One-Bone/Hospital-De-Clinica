document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // MODAL DE CREAR ENCUESTA
    // -------------------------------------------------------------
    const btnCargarEncuesta = document.getElementById('btnCargarEncuesta');
    const modalAltaEncuesta = document.getElementById('modalAltaEncuesta');

    if(btnCargarEncuesta && modalAltaEncuesta) {
        btnCargarEncuesta.addEventListener('click', () => {
            modalAltaEncuesta.classList.add('active');
        });

        // Cerrar al hacer clic fuera del modal
        modalAltaEncuesta.addEventListener('click', (e) => {
            if (e.target === modalAltaEncuesta) {
                modalAltaEncuesta.classList.remove('active');
            }
        });
    }

    // -------------------------------------------------------------
    // MODAL DE ELIMINAR ENCUESTA
    // -------------------------------------------------------------
    const deleteBtns = document.querySelectorAll('.btn-delete-encuesta');
    const deleteEncuestaModal = document.getElementById('deleteEncuestaModal');
    const btnCancelDeleteEncuesta = document.getElementById('btnCancelDeleteEncuesta');
    const deleteEncuestaIdInput = document.getElementById('delete_encuesta_id_input');

    // Asignar el ID correcto al botón de basura que se clickeó
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            deleteEncuestaIdInput.value = btn.getAttribute('data-id');
            deleteEncuestaModal.classList.add('active');
        });
    });

    if(btnCancelDeleteEncuesta) {
        btnCancelDeleteEncuesta.addEventListener('click', () => {
            deleteEncuestaModal.classList.remove('active');
        });
    }

    if(deleteEncuestaModal) {
        deleteEncuestaModal.addEventListener('click', (e) => {
            if (e.target === deleteEncuestaModal) {
                deleteEncuestaModal.classList.remove('active');
            }
        });
    }
});