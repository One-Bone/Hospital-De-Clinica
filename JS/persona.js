document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------
    // MODAL DE ALTA (CREAR PERSONA)
    // -------------------------------------------------------------
    const btnCargarPersona = document.getElementById('btnCargarPersona');
    const modalAltaPersona = document.getElementById('modalAltaPersona');
    const tipoRolSelect = document.getElementById('tipo_rol');
    const camposPaciente = document.getElementById('campos_paciente');
    const camposFuncionario = document.getElementById('campos_funcionario');

    // Abrir Modal
    btnCargarPersona.addEventListener('click', () => {
        modalAltaPersona.classList.add('active');
    });

    // Cerrar al hacer clic fuera
    modalAltaPersona.addEventListener('click', (e) => {
        if (e.target === modalAltaPersona) {
            modalAltaPersona.classList.remove('active');
        }
    });

    // Alternar campos según el rol seleccionado
    tipoRolSelect.addEventListener('change', () => {
        const val = tipoRolSelect.value;
        if (val === 'paciente') {
            camposPaciente.style.display = 'block';
            camposFuncionario.style.display = 'none';
        } else if (val === 'funcionario') {
            camposPaciente.style.display = 'none';
            camposFuncionario.style.display = 'block';
        } else {
            camposPaciente.style.display = 'none';
            camposFuncionario.style.display = 'none';
        }
    });

    // -------------------------------------------------------------
    // MODAL DE EDICIÓN
    // -------------------------------------------------------------
    const editBtns = document.querySelectorAll('.btn-edit-persona');
    const modalEditarPersona = document.getElementById('modalEditarPersona');
    const editTipoRolSelect = document.getElementById('edit_tipo_rol');
    const editCamposPaciente = document.getElementById('edit_campos_paciente');
    const editCamposFuncionario = document.getElementById('edit_campos_funcionario');

    const toggleEditRolFields = (rol) => {
        if (rol === 'paciente') {
            editCamposPaciente.style.display = 'block';
            editCamposFuncionario.style.display = 'none';
        } else if (rol === 'funcionario') {
            editCamposPaciente.style.display = 'none';
            editCamposFuncionario.style.display = 'block';
        } else {
            editCamposPaciente.style.display = 'none';
            editCamposFuncionario.style.display = 'none';
        }
    };

    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Poblar campos del formulario con los data-attributes
            document.getElementById('edit_id_usuario').value = btn.getAttribute('data-id');
            document.getElementById('edit_nombre').value = btn.getAttribute('data-nombre');
            document.getElementById('edit_apellido').value = btn.getAttribute('data-apellido');
            document.getElementById('edit_cedula_identidad').value = btn.getAttribute('data-cedula');
            document.getElementById('edit_email').value = btn.getAttribute('data-email');

            const rol = btn.getAttribute('data-rol');
            editTipoRolSelect.value = rol === 'ninguno' ? 'paciente' : rol;
            toggleEditRolFields(editTipoRolSelect.value);

            // Valores de paciente
            document.getElementById('edit_tel_contacto').value = btn.getAttribute('data-tel') || '';
            document.getElementById('edit_nro_hospital').value = btn.getAttribute('data-nrohospital') || '';

            // Valores de funcionario
            const cargoVal = btn.getAttribute('data-cargo');
            if (cargoVal) {
                document.getElementById('edit_cargo').value = cargoVal;
            }
            document.getElementById('edit_legajo').value = btn.getAttribute('data-legajo') || '';
            document.getElementById('edit_contacto_func').value = btn.getAttribute('data-contactofunc') || '';

            modalEditarPersona.classList.add('active');
        });
    });

    editTipoRolSelect.addEventListener('change', () => {
        toggleEditRolFields(editTipoRolSelect.value);
    });

    modalEditarPersona.addEventListener('click', (e) => {
        if (e.target === modalEditarPersona) {
            modalEditarPersona.classList.remove('active');
        }
    });

    // -------------------------------------------------------------
    // MODAL DE ELIMINACIÓN
    // -------------------------------------------------------------
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