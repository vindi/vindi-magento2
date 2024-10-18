const vindiVr = {
    copyCode: function(button, linkClass, onlyNumbers) {
        let str = document.querySelector(linkClass).innerText;
        if (onlyNumbers) {
            str = str.replace(/[^0-9]+/g, "");
        }
        const originalText = button.innerText;
        const el = document.createElement('textarea');
        el.value = str;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        button.innerText = button.getAttribute('data-text');
        setTimeout(() => {
            button.innerText = originalText;
        }, 5000);
    }
};
