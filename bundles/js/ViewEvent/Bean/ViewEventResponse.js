import {ViewBean} from "../../Bean/ViewBean";
import {ViewEvent} from "./ViewEvent";
import {ViewEventInject} from "./ViewEventInject";

export class ViewEventResponse extends ViewBean {

    /**
     *
     * @type {ViewEvent}
     */
    event = new ViewEvent()

    /**
     *
     * @type {ViewEventInject}
     */
    inject = new ViewEventInject();

    /**
     *
     * @type {string}
     */
    html = '';

    /**
     *
     * @type {object}
     */
    attributes = null;

    fromObject(object) {
        const result = super.fromObject(object);
        if (result.event) {
            result.event = ViewEvent.factory(result.event);
        }
        if (result.inject) {
            result.inject = ViewEventInject.factory(result.inject);
        }
        return result;
    }
}
