import EasyMDE from "easymde";
import 'easymde/dist/easymde.min.css';
import { route } from "../../app.js";

let main = () => {
    let editor = document.querySelector('#editor');
    if (!editor) {
        console.error('No editor element found');
        return;
    }
    let easymde = new EasyMDE({
        element: editor,
        forceSync: true,
        uploadImage: true,
        imageCSRFName: "_token", // Laravel expects a _token by default
        imageCSRFToken: window.app.csrf,
        imageCSRFHeader: false,
        imageUploadEndpoint: route('upload.image'),
        imagePathAbsolute: true,
    });
    if (window.app.debug) {
        console.log(easymde)
    }
}

document.addEventListener('DOMContentLoaded', main);
