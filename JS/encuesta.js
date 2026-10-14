document.addEventListener('DOMContentLoaded', () => {
    const btnCargarEncuesta = document.getElementById('btnCargarEncuesta');
    const modalAltaEncuesta = document.getElementById('modalAltaEncuesta');

    if(btnCargarEncuesta && modalAltaEncuesta) {
        btnCargarEncuesta.addEventListener('click', () => {
            modalAltaEncuesta.classList.add('active');
        });

        modalAltaEncuesta.addEventListener('click', (e) => {
            if (e.target === modalAltaEncuesta) {
                modalAltaEncuesta.classList.remove('active');
            }
        });
    }

    // ELIMINAR
    const deleteBtns = document.querySelectorAll('.btn-delete-encuesta');
    const deleteEncuestaModal = document.getElementById('deleteEncuestaModal');
    const btnCancelDeleteEncuesta = document.getElementById('btnCancelDeleteEncuesta');
    const deleteEncuestaIdInput = document.getElementById('delete_encuesta_id_input');

    
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