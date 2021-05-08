import {OverlayHelper} from "./Helper/OverlayHelper";
import {ViewEvent} from "./Bean/ViewEvent";
import {HandlerFactory} from "./Handler/HandlerFactory";
import {ParameterHelper} from "./Helper/ParameterHelper";

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
                    if (key !== 'path' && key !== '__class') {
                        let debugElementRow = debugElementBody.insertRow()
                        debugElementRow.style.borderBottom = '1px black solid'
                        let cellName = debugElementRow.insertCell();
                        cellName.innerText = key.toUpperCase();
                        cellName.style.fontWeight = 500;
                        cellName.style.borderRight = '1px black solid';
                        debugElementRow.insertCell().innerText = value;
                    }
                }
                let uri = new URL(viewEvent.path, document.baseURI);
                uri.searchParams.forEach((param, name) => {
                    let debugElementRow = debugElementBody.insertRow();
                    debugElementRow.style.borderBottom = '1px black solid';
                    let cellName = debugElementRow.insertCell();
                    cellName.innerText = name.toUpperCase();
                    cellName.style.verticalAlign = 'top';
                    cellName.style.borderRight = '1px black solid';
                    cellName.style.fontWeight = 500;
                    let parameterHelper = new ParameterHelper(name);
                    parameterHelper.fromString(param);
                    let ul = document.createElement('div');
                    parameterHelper.attributes.forEach(attribute => {
                        let li = document.createElement('div');
                        li.innerText = attribute.key + '=' + attribute.value;
                        let br = document.createElement('br');
                        li.append(br);
                        ul.append(li);
                    });
                    debugElementRow.insertCell().append(ul);
                });
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
