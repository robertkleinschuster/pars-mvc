import {ViewEventResponse} from "../Bean/ViewEventResponse";
import {OverlayHelper} from "../Helper/OverlayHelper";
import {ViewEvent} from "../Bean/ViewEvent";
import {ViewInjector} from "../Injector/ViewInjector";
import {ViewEventInjectHtml} from "../Bean/ViewEventInjectHtml";

export class AbstractHandler {

    _event: ViewEvent = null;
    #overlay: OverlayHelper = null;
    #injector: ViewInjector = null;
    _root: ParentNode = null;

    constructor(root: ParentNode, event: ViewEvent) {
        this._event = event;
        this._root = root;
        this.#overlay = new OverlayHelper();
        this.#injector = new ViewInjector(this._root);
    }

    get injector(): ViewInjector {
        return this.#injector;
    }

    trigger(): void {
        console.debug('Trigger event: ', this._event);
        this._triggerDefault(this._event);
    }

    _triggerDefault(viewEvent: ViewEvent): void {
        this._fetch(this._buildFetchUrl(viewEvent), this._buildFetchOptions(viewEvent));
    }

    _buildFetchUrl(viewEvent: ViewEvent): string {
        return (new URL(viewEvent.path, document.baseURI)).toString();
    }

    _buildFetchOptions(viewEvent: ViewEvent): RequestInit {
        return {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-EVENT': JSON.stringify(viewEvent)
            },
        };
    }

    _fetch(url: string, options: RequestInit): void {
        if (!this.#overlay.isVisible()) {
            this.#overlay.show();
            fetch(url, options)
                .then(response => response.headers.get('Content-Type') === 'application/json' ? response.json() : response.text())
                .then(data => {
                    const response = ViewEventResponse.factory(data);
                    console.debug('Handle response:', response);
                    this._handleDebug(response);
                    this._handle(response)
                    this.#overlay.hide();
                })
                .catch(err => {
                    this.#overlay.hide();
                    console.error(err);
                })
        }
    }

    _handleDebug(response) {
        if (response.error) {
            console.error(response.error.type, response.error.message, response.error);
            const transformed = response.error.trace.reduce((acc, {file, ...x}) => { acc[file] = x; return acc}, {})
            console.table(transformed, ['file', 'line', 'function', 'class']);
        }
        if (response.debug) {
            if (Array.isArray(response.debug.data)) {
                response.debug.data.forEach(debug => {
                    console.warn('DEBUG:', debug.object);
                    const transformed = debug.trace.reduce((acc, {file, ...x}) => { acc[file] = x; return acc}, {})
                    console.table(transformed, ['file', 'line', 'function', 'class']);
                });
            } else {
                console.warn(response.debug.data);
            }
        }
    }

    _handle(response: ViewEventResponse): void {
        console.debug('Handle event:', response.event);
        this._handleHtml(response);
        this._handleHistory(response);
        this._handleCache(response);
        this._handleInject(response);
        this._handleAttributes(response);
    }


    _handleHtml(response: ViewEventResponse) {
        if (response.event.path && response.event.target && response.html) {
            response.inject.html.push(ViewEventInjectHtml.factory({
                mode: 'replace',
                selector: response.event.target,
                html: response.html
            }));
        }
    }

    _handleHistory(response: ViewEventResponse) {
        if (response.event.history === true) {
            console.debug('History:', response.event.path, response)
            history.replaceState(response, null, response.event.path);
            history.pushState(response, null, response.event.path);
        }
    }

    _handleCache(response: ViewEventResponse): void {
        if (response.event.deleteCache === true) {
            window.caches.delete('pars-helper');
            console.debug('Deleted cache');
        }
    }

    _handleAttributes(respone: ViewEventResponse): void {
        if (respone && respone.attributes) {
            console.debug('Handle attributes:', respone.attributes);
            if (respone.attributes.redirect_url) {
                window.location = respone.attributes.redirect_url;
            }
        }
    }

    _handleInject(response: ViewEventResponse): void {
        this.injector.inject(response.inject, response.event);
    }
}
