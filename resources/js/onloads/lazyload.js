const EXTRA_CLASSES = ['tw-bg-slate-700', 'tw-opacity-25', 'tw-animate-pulse', 'tw-rounded-md'];

let lazyLoadQueue = {
    queue: [],
    running: false,
    push: function (image) {
        // Give priority to images that are in the viewport
        this.queue.unshift(image);
        this.run();
    },
    run: function () {
        if (this.running === false) {
            this.running = true;
            this.process();
        }
    },
    process: function () {
        if (this.queue.length > 0) {
            let image = this.queue.shift();
            lazyLoad(image).then(() => {
            }).catch(() => {
            }).finally(() => {
                this.process();
            });
        } else {
            this.running = false;
        }
    }
}

function cleanupLazyLoad(image) {
    // Remove data attributes
    image.removeAttribute('data-src');
    image.removeAttribute('data-srcset');
}

function lazyLoad(image) {
    return new Promise((resolve, reject) => {
        // Load image seperately
        let tempImage = new Image();
        tempImage.onload = () => {
            image.src = tempImage.src;
            if (image.dataset.srcset) {
                image.srcset = tempImage.srcset;
            }
            image.classList.remove(...EXTRA_CLASSES);
            if (window.app.debug !== true) {
                cleanupLazyLoad(image);
            }
            resolve();
        }
        tempImage.onerror = () => {
            console.error('Failed to load image', image.dataset.src);
            image.classList.remove(...EXTRA_CLASSES);
            if (window.app.debug !== true) {
                cleanupLazyLoad(image);
            }
            reject();
        }
        tempImage.src = image.dataset.src;
        if (image.dataset.srcset) {
            tempImage.srcset = image.dataset.srcset;
        }
    });
}

function queueLazyLoad(image) {
    lazyLoadQueue.push(image);
}

function handleIntersect(entries, observer) {
    for (let entry of entries) {
        if (entry.isIntersecting) {
            let image = entry.target;
            queueLazyLoad(image);
            observer.unobserve(image);
        }
    }
    if (entries.length === 0) {
        // Disconnect the observer when done
        observer.disconnect();
    }
}

/**
 * Singleton function to create an IntersectionObserver
 */
let imageObserver = (function () {
    let observer = null;
    return () => {
        if (!observer) {
            observer = new IntersectionObserver(handleIntersect, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
        }
        return observer;
    };
})();

function setupLazyLoadImages(element = null) {
    if (!element) {
        element = document;
    }
    let images = element.querySelectorAll('img[data-src]');
    images.forEach((image) => {
        image.classList.add(...EXTRA_CLASSES);
        imageObserver().observe(image);
    });
}

function setupLazyLoad() {
    setupLazyLoadImages();
}

document.addEventListener('DOMContentLoaded', () => {
    setupLazyLoad();
});

export {setupLazyLoadImages};
