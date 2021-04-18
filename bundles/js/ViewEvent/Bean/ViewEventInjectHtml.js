import {ViewBean} from "../../Bean/ViewBean";

export class ViewEventInjectHtml extends ViewBean {
    /**
     *
     * @type {string}
     */
    html = '';
    /**
     *
     * @type {?string}
     */
    mode = null;
    /**
     *
     * @type {?string}
     */
    selector = null;

    /**
     *
     * @return {string}
     * @constructor
     */
    static get MODE_REPLACE()
    {
        return 'replace';
    }

    /**
     *
     * @return {string}
     * @constructor
     */
    static get MODE_APPEND()
    {
        return 'append';
    }

    /**
     *
     * @return {string}
     * @constructor
     */
    static get MODE_PREPEND()
    {
        return 'prepend';
    }

}
