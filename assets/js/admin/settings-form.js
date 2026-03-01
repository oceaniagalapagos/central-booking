syncCodeMirrorEditors();

function syncCodeMirrorEditors() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        if (textarea.nextSibling && textarea.nextSibling.classList &&
            textarea.nextSibling.classList.contains('CodeMirror')) {
            const editor = textarea.nextSibling.CodeMirror;
            if (editor) {
                editor.save();
            }
        }
    });
}