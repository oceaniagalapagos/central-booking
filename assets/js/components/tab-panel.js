class GitTabPanel {
    constructor(container) {
        this.container = container;
        this.id = container.id || this.generateUniqueId();

        this.tabs = this.getDirectChildTabs();
        this.contents = this.getDirectChildContents();

        this.init();
    }

    generateUniqueId() {
        return 'git-tab-panel-' + Math.random().toString(36).substr(2, 9);
    }

    getDirectChildTabs() {
        const tabNav = this.container.querySelector(':scope > .tab-nav, :scope > .git-tab-nav');
        if (!tabNav) {
            console.warn('No se encontró .tab-nav en el contenedor:', this.container);
            return [];
        }
        return Array.from(tabNav.querySelectorAll(':scope > li'));
    }

    getDirectChildContents() {
        const tabContentContainer = this.container.querySelector(':scope > .tab-content-container') || this.container;
        return Array.from(tabContentContainer.querySelectorAll(':scope > .tab-content'));
    }

    init() {
        if (this.tabs.length === 0) {
            console.warn('No se encontraron pestañas en:', this.container);
            return;
        }

        this.ensureUniqueIds();

        this.tabs.forEach((tab, index) => {
            tab.addEventListener('click', (e) => this.switchTab(e, tab));

            tab.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.switchTab(e, tab);
                } else if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.navigateWithKeyboard(e.key, index);
                }
            });

            tab.setAttribute('role', 'tab');
            tab.setAttribute('tabindex', index === 0 ? '0' : '-1');
        });

        this.contents.forEach(content => {
            content.setAttribute('role', 'tabpanel');
        });

        const activeTab = this.tabs.find(tab => tab.classList.contains('active'));
        if (!activeTab && this.tabs.length > 0) {
            this.activateTab(this.tabs[0]);
        }
    }

    ensureUniqueIds() {
        this.tabs.forEach((tab, index) => {
            if (!tab.dataset.tab) {
                const contentId = `${this.id}-content-${index}`;
                tab.dataset.tab = `#${contentId}`;

                if (this.contents[index] && !this.contents[index].id) {
                    this.contents[index].id = contentId;
                }
            }
        });
    }

    switchTab(event, tab) {
        event.preventDefault();
        event.stopPropagation();
        const targetId = tab.dataset.tab;
        if (!targetId) {
            console.warn('Tab sin data-tab:', tab);
            return;
        }

        const targetContent = this.findTargetContent(targetId);
        if (!targetContent) {
            console.warn('Contenido no encontrado para:', targetId, 'en contenedor:', this.container);
            return;
        }

        this.activateTab(tab);

        this.container.dispatchEvent(new CustomEvent('git:tabChanged', {
            detail: {
                tab,
                content: targetContent,
                panelId: this.id
            },
            bubbles: false
        }));
    }

    findTargetContent(targetId) {
        const cleanId = targetId.replace('#', '');
        let target = this.container.querySelector(`#${cleanId}`);
        if (!target) {
            target = this.contents.find(content =>
                content.id === cleanId ||
                content.dataset.tabId === cleanId
            );
        }

        return target;
    }

    activateTab(activeTab) {
        const targetContent = this.findTargetContent(activeTab.dataset.tab);
        this.tabs.forEach(tab => {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
            tab.setAttribute('tabindex', '-1');
        });

        this.contents.forEach(content => {
            content.classList.remove('active');
            content.setAttribute('aria-hidden', 'true');
        });
        activeTab.classList.add('active');
        activeTab.setAttribute('aria-selected', 'true');
        activeTab.setAttribute('tabindex', '0');
        activeTab.focus();

        if (targetContent) {
            targetContent.classList.add('active');
            targetContent.setAttribute('aria-hidden', 'false');
        }
    }

    navigateWithKeyboard(key, currentIndex) {
        let newIndex;

        if (key === 'ArrowLeft') {
            newIndex = currentIndex > 0 ? currentIndex - 1 : this.tabs.length - 1;
        } else if (key === 'ArrowRight') {
            newIndex = currentIndex < this.tabs.length - 1 ? currentIndex + 1 : 0;
        }

        if (newIndex !== undefined) {
            this.activateTab(this.tabs[newIndex]);
        }
    }

    setActiveTab(tabIndex) {
        if (this.tabs[tabIndex]) {
            this.activateTab(this.tabs[tabIndex]);
        }
    }

    getActiveTab() {
        return this.tabs.find(tab => tab.classList.contains('active'));
    }

    getActiveTabIndex() {
        const activeTab = this.getActiveTab();
        return activeTab ? this.tabs.indexOf(activeTab) : -1;
    }

    destroy() {
        this.tabs.forEach(tab => {
            tab.removeEventListener('click', this.switchTab);
            tab.removeEventListener('keydown', this.navigateWithKeyboard);
        });
    }
}

class GitTabPanelManager {
    constructor() {
        this.panels = new Map();
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initializePanels();
        });
    }

    initializePanels() {
        const tabPanes = document.querySelectorAll('.git-tab-pane');

        tabPanes.forEach((pane, index) => {
            if (!pane.id) {
                pane.id = `git-tab-pane-${index}`;
            }

            if (!this.panels.has(pane.id)) {
                const panel = new GitTabPanel(pane);
                this.panels.set(pane.id, panel);

                pane.addEventListener('git:tabChanged', (e) => {
                    if (e.detail.content.dataset.loadUrl) {
                        this.loadTabContent(e.detail.content);
                    }
                });
            }
        });
    }

    loadTabContent(contentElement) {
        const url = contentElement.dataset.loadUrl;

        if (url && !contentElement.dataset.loaded) {
            contentElement.innerHTML = `
                <div class="git-loading">
                    <div class="spinner"></div>
                    <p>Cargando contenido...</p>
                </div>
            `;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(html => {
                    contentElement.innerHTML = html;
                    contentElement.dataset.loaded = 'true';

                    this.initializeLoadedContent(contentElement);
                })
                .catch(error => {
                    contentElement.innerHTML = `
                    <div class="git-error">
                        <p>Error al cargar contenido</p>
                        <button onclick="this.parentElement.parentElement.removeAttribute('data-loaded')" 
                                class="button">Reintentar</button>
                    </div>
                `;
                    console.error('Error loading tab content:', error);
                });
        }
    }

    initializeLoadedContent(container) {
        const nestedPanes = container.querySelectorAll('.git-tab-pane');
        nestedPanes.forEach(pane => {
            if (!this.panels.has(pane.id)) {
                const panel = new GitTabPanel(pane);
                this.panels.set(pane.id, panel);
            }
        });
    }

    getPanel(panelId) {
        return this.panels.get(panelId);
    }

    destroyPanel(panelId) {
        const panel = this.panels.get(panelId);
        if (panel) {
            panel.destroy();
            this.panels.delete(panelId);
        }
    }
}

window.gitTabManager = new GitTabPanelManager();

window.GitTabPanel = GitTabPanel;