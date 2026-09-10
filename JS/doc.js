// Script open file modal
document.addEventListener('DOMContentLoaded', () => {
            const crgBtn = document.getElementById('crgBtn');
            const chargeModal = document.getElementById('chargeModal');

            // Open
            crgBtn.addEventListener('click', () => {
                chargeModal.classList.add('active');
            });

            // Close click outside
            chargeModal.addEventListener('click', (e) => {
                if (e.target === chargeModal) {
                    chargeModal.classList.remove('active');
                }
            });
        });

document.addEventListener('DOMContentLoaded', () => {
            const deleteBtns = document.querySelectorAll('.btn-delete-doc');
            const deleteModal = document.getElementById('deleteModal');
            const btnCancelDelete = document.getElementById('btnCancelDelete');
            const deleteDocIdInput = document.getElementById('delete_doc_id_input');

            // Open modal, asign ID
            deleteBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Read ID from atribute 'data-id'
                    // got it into input hidden
                    const docId = btn.getAttribute('data-id');
                    deleteDocIdInput.value = docId;
                    deleteModal.classList.add('active');
                });
            });

            // Click on Cancel to close
            btnCancelDelete.addEventListener('click', () => {
                deleteModal.classList.remove('active');
            });

            // Click outside to close
            deleteModal.addEventListener('click', (e) => {
                if (e.target === deleteModal) {
                    deleteModal.classList.remove('active');
                }
            });
        });