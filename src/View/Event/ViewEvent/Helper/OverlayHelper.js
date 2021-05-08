import {HtmlHelper} from "./HtmlHelper";
import "./OverlayHelper.scss"
export class OverlayHelper {

    constructor() {
        this.visible = false;
    }


    show(target) {
        const body = document.body;
        if (document.querySelectorAll(target).length) {
            document.querySelectorAll(target).forEach(function (element) {
                element.style.opacity = 0.5;
            });
        } else {
            if (document.querySelectorAll('.ajax-overlay').length === 0) {
                if (!body) return;
                let html = '<div class="overlay text-center ajax-overlay">' +
                    '<div style="width: 7rem; height: 7rem;" class="spinner-grow text-light shadow-lg" role="status">\n' +
                    '  <span class="sr-only">Loading...</span>\n' +
                    '</div></div>';
                body.append(HtmlHelper.createElementFromHTML(html));
            }
            document.querySelectorAll('.ajax-overlay').forEach(element => element.classList.add('show'));
        }
        this.visible = true;
    }

    progress(target, progress) {
       // change opacity with progress
    }

    hide(target) {
        document.querySelectorAll('.ajax-overlay').forEach(element => element.classList.remove('show'));
        this.visible = false;
    }

    isVisible() {
        return this.visible;
    }
}
