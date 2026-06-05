import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        apiKey: String
    }

    async connect() {
        await this.#loadScript();
        tinymce.init({
            target: this.element,
            plugins: 'advlist autolink lists link image code fullscreen table wordcount',
            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code fullscreen',
            height: 500,
            setup: (editor) => {
                editor.on('change', () => editor.save());
            }
        });
    }

    disconnect() {
        if (window.tinymce) {
            tinymce.remove(this.element);
        }
    }

    #loadScript() {
        if (window.tinymce) {
            return Promise.resolve();
        }
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = `https://cdn.tiny.cloud/1/${this.apiKeyValue}/tinymce/7/tinymce.min.js`;
            script.referrerPolicy = 'origin';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }
}
