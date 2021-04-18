import {OverlayHelper} from "./Helper/OverlayHelper";
import {ViewEvent} from "./Bean/ViewEvent";
import {HandlerFactory} from "./Handler/HandlerFactory";

export class ViewEventHandler {

    #root: ParentNode = document;
    #_initialized = false;
    #overlay = null;

    listeners = [];

    constructor(root: ParentNode) {
        this.#overlay = new OverlayHelper();
        this.#root = root;
    }

    init(): this {
        this.#_initialized = true;
        this.#root.querySelectorAll('[data-event]')
            .forEach(this.#attachEvents.bind(this));
        this.listeners.push(this.#injectorCallback.bind(this));
        return this;
    }

    get isInitialized() {
        return this.#_initialized;
    }

    #attachEvents(element: HTMLElement) {
        if (element && element.matches('[data-event]')) {
            const viewEvent: ViewEvent = ViewEvent.factory(element.dataset.event);
            console.debug('%cAttached event: ', 'color: lightgrey;', viewEvent);
            this.triggerListener = (event) => {
                if (viewEvent.delegate === null || event.target.closest(viewEvent.delegate)) {
                    event.preventDefault();
                    this.#triggerEvent(viewEvent);
                }
            }
            element.removeEventListener(viewEvent.trigger, this.triggerListener);
            element.addEventListener(viewEvent.trigger, this.triggerListener.bind(this));
        }
    }

    /**
     *
     * @param {ViewEvent} viewEvent
     */
    #triggerEvent(viewEvent: ViewEvent) {
        const handler = HandlerFactory.create(this.#root, viewEvent);
        handler.injector.listeners = this.listeners;
        handler.trigger();
    }

    #injectorCallback(newElement) {
        if (newElement.matches('[data-event]')) {
            this.#attachEvents(newElement);
        }
        newElement.querySelectorAll('[data-event]').forEach(newSubElement => {
                this.#attachEvents(newSubElement);
            }
        );
    }
}
