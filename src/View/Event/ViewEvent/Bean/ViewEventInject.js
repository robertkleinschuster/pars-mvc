import {ViewBean} from "../../Bean/ViewBean";
import {ViewEventInjectHtml} from "./ViewEventInjectHtml";
import {ViewEventInjectScript} from "./ViewEventInjectScript";

export class ViewEventInject extends ViewBean {
    /**
     *
     * @return {string}
     * @constructor
     */
    static get TYPE_HTML() {
        return 'html';
    }

    /**
     *
     * @return {string}
     * @constructor
     */
    static get TYPE_SCRIPT() {
        return 'html';
    }

    /**
     *
     * @type {ViewEventInjectHtml[]}
     */
    html = [];
    /**
     *
     * @type {ViewEventInjectScript[]}
     */
    script = [];


    fromObject(object) {
        const result = super.fromObject(object);
        if (result.html) {
            const html = result.html;
            result.html = [];
            html.forEach(item => result.html.push(ViewEventInjectHtml.factory(item)));
        }
        if (result.script) {
            const script = result.script;
            result.script = [];
            script.forEach(item => result.script.push(ViewEventInjectScript.factory(item)));
        }
        return result;
    }
}
