document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.git-tab-container').forEach(container => {
        const tabButtons = container.querySelectorAll('.git-tab-button');
        const tabPanels = container.querySelectorAll('.git-tab-panel');

        tabButtons.forEach(button => {
            button.addEventListener('click', function () {
                const tabId = this.dataset.tab;

                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanels.forEach(panel => panel.classList.remove('active'));

                this.classList.add('active');

                container.querySelector(`#${tabId}`).classList.add('active');
            });
        });
    });
});