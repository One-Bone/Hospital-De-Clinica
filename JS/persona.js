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


// Edit person
const editBtns = document.querySelectorAll('.btn-edit-persona');
const editPersonaModal = document.getElementById('editPersonaModal');
const editCamposPaciente = document.getElementById('edit_campos_paciente');
const editTiposRolSelect = document.getElementById('edit_tipo_rol');
const editCamposFuncionario = document.getElementById('edit_campos_funcionario');

editBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_persona_id').value = btn.getAttribute('data-id');
        document.getElementById('edit_nombre').value = btn.getAttribute('data-nombre');
        document.getElementById('edit_apellido').value = btn.getAttribute('data-apellido');
        document.getElementById('edit_cedula_identidad').value = btn.getAttribute('data-cedula');
        document.getElementById('edit_email').value = btn.getAttribute('data-email');
        document.getElementById('edit_tel_contacto').value = btn.getAttribute('data-tel');
        document.getElementById('edit_nro_hospital').value = btn.getAttribute('data-nrohospital');
        document.getElementById('edit_cargo').value = btn.getAttribute('data-cargo') || 'Medico';
        document.getElementById('edit_legajo').value = btn.getAttribute('data-legajo');
        document.getElementById('edit_contacto_func').value = btn.getAttribute('data-contactofunc');

        const rol = btn.getAttribute('data-rol');
        editTiposRolSelect.value = rol;
        editTiposRolSelect.dispatchEvent(new Event('change'));

        editPersonaModal.classList.add('active');
    });
});

editTiposRolSelect.addEventListener('change', () => {
    const val = editTiposRolSelect.value;

    if (val === 'paciente') {
        editCamposPaciente.style.display = 'block';
        editCamposFuncionario.style.display = 'none';

        const inputsFuncionario = editCamposFuncionario.querySelectorAll('input, select');
        inputsFuncionario.forEach(input => input.disabled = true);

        const inputsPaciente = editCamposPaciente.querySelectorAll('input, select');
        inputsPaciente.forEach(input => input.disabled = false);

    } else if (val === 'funcionario') {
        editCamposPaciente.style.display = 'none';
        editCamposFuncionario.style.display = 'block';

        const inputsFuncionario = editCamposFuncionario.querySelectorAll('input, select');
        inputsFuncionario.forEach(input => input.disabled = false);

        const inputsPaciente = editCamposPaciente.querySelectorAll('input, select');
        inputsPaciente.forEach(input => input.disabled = true);
    }
});

editPersonaModal.addEventListener('click', (e) => {
        if (e.target === editPersonaModal) {
            editPersonaModal.classList.remove('active');
        }
    });
});
