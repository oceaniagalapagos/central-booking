export function createThirdContainer(left, center, right) {
    const container = document.createElement('div');
    container.classList.add('third-container');

    const sections = [
        { content: right, className: 'left' },
        { content: center, className: 'center' },
        { content: left, className: 'right' },
    ];

    sections.forEach(({ content, className }) => {
        const section = document.createElement('div');
        section.classList.add(className);

        if (content instanceof Node) {
            section.appendChild(content);
        } else {
            section.textContent = String(content);
        }

        container.appendChild(section);
    });

    return container;
}