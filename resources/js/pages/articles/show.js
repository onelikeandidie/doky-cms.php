let setupHeadingLinks = () => {
    /** @type {NodeListOf<HTMLSpanElement>} */
    let headingLinks = document.querySelectorAll('.heading-link');
    headingLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            // Get the target element
            let target = e.originalTarget || e.target;
            // Get the data-url attribute
            let url = target.dataset.url;
            if (url.startsWith('#')) {
                let documentUrl = window.location.href;
                documentUrl = documentUrl.split('#')[0];
                url = documentUrl + url;
            }
            // Copy the url to the clipboard
            navigator.clipboard.writeText(url);
        });
    });
}

let main = () => {
    setupHeadingLinks();
}

document.addEventListener('DOMContentLoaded', main);
