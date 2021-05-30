import {ViewEventResponse} from "../Bean/ViewEventResponse";
import {OverlayHelper} from "../Helper/OverlayHelper";
import {ViewEvent} from "../Bean/ViewEvent";
import {ViewInjector} from "../Injector/ViewInjector";
import {ViewEventInjectHtml} from "../Bean/ViewEventInjectHtml";
import fetchProgress from "fetch-progress";

export class AbstractHandler {

    _event: ViewEvent = null;
    #overlay: OverlayHelper = null;
    #injector: ViewInjector = null;
    _root: ParentNode = null;
    redirect = null;

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
        if (window.debug) {
            console.debug('%cTrigger event: ', 'color: lime; font-weight: bold;font-size: 16px', this._event);
            console.time("Trigger");
        }
        this._triggerDefault(this._event);
    }

    _triggerDefault(viewEvent: ViewEvent): void {
        this._fetch(this._buildFetchUrl(viewEvent), this._buildFetchOptions(viewEvent));
    }

    _buildFetchUrl(viewEvent: ViewEvent): string {
        let url = new URL(viewEvent.path, document.baseURI);
        return url.toString();
    }

    _buildFetchOptions(viewEvent: ViewEvent): RequestInit {
        let formData = new FormData();
        formData.set('pars-view-event-data', JSON.stringify(viewEvent))
        return {
            headers: {'pars-ajax': 'true'},
            method: 'post',
            body: formData
        };
    }

    _fetch(url: string, options: RequestInit): void {
        if (!this.#overlay.isVisible()) {
            this.#overlay.show(this._event.target);
            if (window.debug) {
                console.debug('%cFetch:', 'color: green;font-weight: bold;font-size: 16px;', url, options)
                console.time("Fetch");
            }

            fetch(url, options)
                .then(
                    fetchProgress({
                        onProgress(progress) {
                            this.#overlay.progress(this._event.target, progress);
                        }
                    })
                )
                .then(response => response.clone().headers.get('Content-Type') === 'application/json' ? response.clone().json() : response.clone().text())
                .then(data => {
                    if (window.debug) {
                        console.timeEnd("Fetch");
                    }
                    const response = ViewEventResponse.factory(data);
                    if (window.debug) {
                        console.debug('Handle response:', response);
                    }
                    if (window.debug) {
                        this._handleDebug(response);
                    }
                    if (this._handleAttributes(response)) {
                        this._handle(response);
                        if (window.debug) {
                            console.timeEnd("Trigger");
                        }
                        this.#overlay.hide(this._event.target);
                    }
                })
                .catch(err => {
                    this.#overlay.hide(this._event.target);
                    if (window.debug) {
                        console.error(err);
                    }
                })
        }
    }

    _handleDebug(response) {
        if (response.error) {

            console.error(response.error.type, response.error.message, response.error);
            const transformed = response.error.trace.reduce((acc, {file, ...x}) => {
                acc[file] = x;
                return acc
            }, {})
            console.table(transformed, ['file', 'line', 'function', 'class']);
        }
        if (response.debug) {
            if (Array.isArray(response.debug.data)) {
                response.debug.data.forEach(debug => {
                    console.warn('DEBUG:', debug.object);
                    const transformed = debug.trace.reduce((acc, {file, ...x}) => {
                        acc[file] = x;
                        return acc
                    }, {})
                    console.table(transformed, ['file', 'line', 'function', 'class']);
                });
            } else {
                console.warn(response.debug.data);
            }
        }
    }

    _handle(response: ViewEventResponse): void {
        if (window.debug) {
            console.debug('Handle event:', response.event);
        }
        this._handleHtml(response);
        this._handleHistory(response);
        this._handleCache(response);
        this._handleInject(response);
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
            if (window.debug) {
                console.debug('%cHistory:', 'color: DarkRed; font-weight: bold', response.event.path, response)

            }
            history.replaceState({event: {path: window.location.href}}, null, window.location.href);
            history.pushState(response, null, response.event.path);
        }
    }

    _handleCache(response: ViewEventResponse): void {
        if (response.event.deleteCache === true) {
            window.caches.delete('pars-helper');
            if (window.debug) {

                console.debug('%cDeleted cache', 'color: red; font-weight: bold;');
            }
        }
    }

    _handleAttributes(response: ViewEventResponse): boolean {
        if (response && response.attributes) {
            if (window.debug) {
                console.debug('Handle attributes:', response.attributes);
            }
            if (response.attributes.redirect_url) {
                this.#overlay.hide(this._event.target);
                response.inject = null;
                const event = response.event;
                event.form = null;
                event.type = ViewEvent.TYPE_LINK;
                event.path = response.attributes.redirect_url;
                if (this.redirect) {
                    this.redirect(event);
                }
                return false;
            }
        }
        return true;
    }

    _handleInject(response: ViewEventResponse): void {
        if (response.inject) {
            this.injector.inject(response.inject, response.event);
        }
    }
}
