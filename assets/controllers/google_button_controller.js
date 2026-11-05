import { Controller } from '@hotwired/stimulus';

/*
 * Draws the "Sign in with Google" button in the site's theme: Google's dark button
 * (filled_black) with the dark theme, the white one (outline) with the light theme, and draws
 * it again when the theme changes. The button's other options are its data attributes, as in
 * Google's HTML API (the g_id_onload element initializes the sign-in).
 *
 * <div class="g_id_signin" data-controller="google-button" data-size="large" …></div>
 */
export default class extends Controller {
    connect() {
        this.render();
        this.observer = new MutationObserver(() => this.render());
        this.observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
    }

    disconnect() {
        this.observer.disconnect();
    }

    /** Google's theme for the site's theme (color-modes.js sets data-bs-theme on <html>). */
    get theme() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'filled_black' : 'outline';
    }

    /**
     * Sets the theme for Google's script, which reads it from the data attribute when it draws
     * the button; a button it already drew is drawn again.
     */
    render() {
        this.element.dataset.theme = this.theme;
        if (!window.google?.accounts?.id || !this.element.childElementCount) {
            return;
        }

        const { type, size, theme, text, shape, logo_alignment, width, locale } = this.element.dataset;
        this.element.replaceChildren();
        window.google.accounts.id.renderButton(this.element, { type, size, theme, text, shape, logo_alignment, width, locale });
    }
}
