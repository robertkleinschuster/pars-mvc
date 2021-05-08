import {OverlayHelper} from "./Helper/OverlayHelper";
import {ViewEvent} from "./Bean/ViewEvent";
import {HandlerFactory} from "./Handler/HandlerFactory";
import {PathHelper} from "./Helper/PathHelper";

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
            if (window.debug) {
            console.debug('%cAttached event: ', 'color: lightgrey;', viewEvent);

            }
            this.triggerListener = (event) => {
                if (viewEvent.delegate === null || event.target.closest(viewEvent.delegate)) {
                    event.preventDefault();
                    this.#triggerEvent(viewEvent);
                }
            }
            element.removeEventListener(viewEvent.trigger, this.triggerListener);
            element.addEventListener(viewEvent.trigger, this.triggerListener.bind(this));
            if (window.debugView) {
                let debugElement = document.createElement('table');
                debugElement.classList.add('text-monospace');
                debugElement.style.fontSize = '8px';
                let debugElementBody = debugElement.createTBody();
                for (const [key, value] of Object.entries(viewEvent)) {
                    let debugElementRow = debugElementBody.insertRow()
                    debugElementRow.insertCell().innerText = key;
                    debugElementRow.insertCell().innerText = value;
                }
                debugElement.classList.add('pars-debug');
                this.eventDebugShow = (event) => {
                    if (viewEvent.delegate === null || event.target.closest(viewEvent.delegate)) {
                        document.body.append(debugElement);
                    }
                }
                element.removeEventListener('mouseover', this.eventDebugShow);
                element.addEventListener('mouseover', this.eventDebugShow.bind(this));
                this.eventDebugHide = (event) => {
                    document.querySelectorAll('.pars-debug').forEach(element => element.remove());
                }
                element.removeEventListener('mouseleave', this.eventDebugHide);
                element.addEventListener('mouseleave', this.eventDebugHide.bind(this));
            }
        }
    }

    /**
     *
     * @param {ViewEvent} viewEvent
     */
    #triggerEvent(viewEvent: ViewEvent) {
        const handler = HandlerFactory.create(this.#root, viewEvent);
        handler.redirect = this.#triggerEvent.bind(this);
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
