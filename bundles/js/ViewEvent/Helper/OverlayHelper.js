import {HtmlHelper} from "./HtmlHelper";
import "../../../scss/overlay.scss"
export class OverlayHelper {

    constructor() {
        this.visible = false;
    }


    show() {
        if (document.querySelectorAll('.ajax-overlay').length === 0) {
            const body = document.body;
            if (!body) return;
            let html = '<div class="overlay text-center ajax-overlay">' +
                '<div style="width: 7rem; height: 7rem;" class="spinner-grow text-light shadow-lg" role="status">\n' +
                '  <span class="sr-only">Loading...</span>\n' +
                '</div></div>';
            body.append(HtmlHelper.createElementFromHTML(html));
        }
        document.querySelectorAll('.ajax-overlay').forEach(element => element.classList.add('show'));
        this.visible = true;
    }

    hide() {
        document.querySelectorAll('.ajax-overlay').forEach(element => element.classList.remove('show'));
        this.visible = false;
    }

    isVisible() {
        return this.visible;
    }
}
