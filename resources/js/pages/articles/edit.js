import EasyMDE from "easymde";
import 'easymde/dist/easymde.min.css';

let main = () => {
    let editor = document.querySelector('#editor');
    if (!editor) {
        console.error('No editor element found');
        return;
    }
    let easymde = new EasyMDE({
        element: editor,
        forceSync: true,
    });
}

document.addEventListener('DOMContentLoaded', main);
