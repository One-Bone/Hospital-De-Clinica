document.addEventListener('DOMContentLoaded', () => {
    // Create person
    const btnCargarPersona = document.getElementById('btnCargarPersona');
    const modalAltaPersona = document.getElementById('modalAltaPersona');
    const tipoRolSelect = document.getElementById('tipo_rol');
    const camposPaciente = document.getElementById('campos_paciente');
    const camposFuncionario = document.getElementById('campos_funcionario');

    // Open modal
    btnCargarPersona.addEventListener('click', () => {
        modalAltaPersona.classList.add('active');
    });

    // Close click out
    modalAltaPersona.addEventListener('click', (e) => {
        if (e.target === modalAltaPersona) {
            modalAltaPersona.classList.remove('active');
        }
    });

    // Alternate based on role
    tipoRolSelect.addEventListener('change', () => {
        const val = tipoRolSelect.value;
        
        if (val === 'paciente') {
            camposPaciente.style.display = 'block';
            camposFuncionario.style.display = 'none';
            
            // Disable inputs hidden to avoid browser blocking
            const inputsFuncionario = camposFuncionario.querySelectorAll('input, select');
            inputsFuncionario.forEach(input => input.disabled = true);
            
            const inputsPaciente = camposPaciente.querySelectorAll('input, select');
            inputsPaciente.forEach(input => input.disabled = false);

        } else if (val === 'funcionario') {
            camposPaciente.style.display = 'none';
            camposFuncionario.style.display = 'block';
            
            const inputsPaciente = camposPaciente.querySelectorAll('input, select');
            inputsPaciente.forEach(input => input.disabled = true);
            
            const inputsFuncionario = camposFuncionario.querySelectorAll('input, select');
            inputsFuncionario.forEach(input => input.disabled = false);
        }
    });

    // Delete person
    const deleteBtns = document.querySelectorAll('.btn-delete-persona');
    const deletePersonaModal = document.getElementById('deletePersonaModal');
    const btnCancelDeletePersona = document.getElementById('btnCancelDeletePersona');
    const deletePersonaIdInput = document.getElementById('delete_persona_id_input');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            deletePersonaIdInput.value = btn.getAttribute('data-id');
            deletePersonaModal.classList.add('active');
        });
    });

    btnCancelDeletePersona.addEventListener('click', () => {
        deletePersonaModal.classList.remove('active');
    });

    deletePersonaModal.addEventListener('click', (e) => {
        if (e.target === deletePersonaModal) {
            deletePersonaModal.classList.remove('active');
        }
    });
});