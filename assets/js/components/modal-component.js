class ModalSystem {
    constructor() {
        this.init();
    }

    init() {
        // Vincular eventos para abrir modales
        document.querySelectorAll('[data-modal-target]').forEach(button => {
            button.addEventListener('click', (e) => {
                const modalId = e.target.getAttribute('data-modal-target');
                this.openModal(modalId);
            });
        });

        // Vincular eventos para cerrar modales
        document.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', (e) => {
                const modalId = e.target.getAttribute('data-modal-close');
                this.closeModal(modalId);
            });
        });

        // Cerrar modal al hacer clic en el overlay
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal.id);
                }
            });
        });

        // Cerrar modal con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeTopModal();
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Evitar scroll del body
        document.body.style.overflow = 'hidden';

        // Mostrar modal
        modal.style.display = 'flex';

        // Trigger animación
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);

        // Enfocar primer elemento focusable
        this.focusFirstElement(modal);
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Quitar clase de mostrar
        modal.classList.remove('show');

        // Esperar animación y ocultar
        setTimeout(() => {
            modal.style.display = 'none';

            // Restaurar scroll si no hay más modales abiertos
            const openModals = document.querySelectorAll('.modal.show');
            if (openModals.length === 0) {
                document.body.style.overflow = '';
            }
        }, 300);
    }

    closeTopModal() {
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length > 0) {
            const topModal = openModals[openModals.length - 1];
            this.closeModal(topModal.id);
        }
    }

    focusFirstElement(modal) {
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
    }

    // Métodos públicos para usar desde JavaScript externo
    static open(modalId) {
        const instance = new ModalSystem();
        instance.openModal(modalId);
    }

    static close(modalId) {
        const instance = new ModalSystem();
        instance.closeModal(modalId);
    }
}

// Inicializar sistema de modales
document.addEventListener('DOMContentLoaded', () => {
    new ModalSystem();
});

// Funciones globales para fácil acceso
function openModal(modalId) {
    ModalSystem.open(modalId);
}

function closeModal(modalId) {
    ModalSystem.close(modalId);
}