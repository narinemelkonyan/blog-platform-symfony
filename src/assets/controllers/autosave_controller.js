import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        saveUrl:   String,
        updateUrl: String,
        interval:  { type: Number, default: 30000 },
    }

    static targets = ['title', 'content', 'status']

    #timer          = null;
    #articleId      = null;
    #lastSavedTitle = null;
    #lastSavedContent = null;

    connect() {
        if (this.hasUpdateUrlValue && this.updateUrlValue) {
            this.#articleId = true;
            this.#lastSavedTitle   = this.hasTitleTarget ? this.titleTarget.value : null;
            this.#lastSavedContent = this.#getContent();
        }

        this.#timer = setInterval(() => this.#autosave(), this.intervalValue);
    }

    disconnect() {
        clearInterval(this.#timer);
    }

    async #autosave() {
        if (!this.hasTitleTarget || !this.hasContentTarget) {
            return;
        }

        const title   = this.titleTarget.value.trim();
        const content = this.#getContent();

        if (title === this.#lastSavedTitle && content === this.#lastSavedContent) {
            return;
        }

        if (!title && !content) {
            return;
        }

        this.#showStatus('Saving...');

        try {
            let url;
            if (this.#articleId === true) {
                url = this.updateUrlValue;
            } else if (this.#articleId) {
                url = this.saveUrlValue.replace('/new', `/${this.#articleId}/update`);
            } else {
                url = this.saveUrlValue;
            }

            const response = await fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ title, content }),
            });

            const data = await response.json();

            if (data.success) {
                this.#lastSavedTitle   = title;
                this.#lastSavedContent = content;

                if (!this.#articleId && data.id) {
                    this.#articleId = data.id;
                }

                this.#showStatus(`Draft saved at ${data.savedAt}`);
            } else {
                this.#showStatus(data.message, true);
            }
        } catch {
            this.#showStatus('No connection', true);
        }
    }

    #getContent() {
        const editor = window.tinymce?.activeEditor;
        return editor ? editor.getContent() : this.contentTarget.value.trim();
    }

    #showStatus(message, isError = false) {
        if (!this.hasStatusTarget) return;
        this.statusTarget.textContent = message;
        this.statusTarget.className   = isError
            ? 'autosave-status text-danger'
            : 'autosave-status text-muted';
    }
}
