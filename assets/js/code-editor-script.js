function handleTabPress(event) {
    const textarea = event.target;

    if (event.key === 'Tab') {
        event.preventDefault();

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        if (event.shiftKey) {
            const valueBefore = textarea.value.substring(0, start);
            const valueAfter = textarea.value.substring(end);

            if (valueBefore.endsWith('\t')) {
                textarea.value = valueBefore.slice(0, -1) + valueAfter;

                textarea.selectionStart = textarea.selectionEnd = start - 1;
            }
        } else {
            textarea.value = textarea.value.substring(0, start) + '\t' + textarea.value.substring(end);

            textarea.selectionStart = textarea.selectionEnd = start + 1;
        }
    }
}
