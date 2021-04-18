import {OverlayHelper} from "./Helper/OverlayHelper";
import {HtmlHelper} from "./Helper/HtmlHelper";
import {ViewEvent} from "./Bean/ViewEvent";
import {ViewEventResponse} from "./Bean/ViewEventResponse";
import {ViewEventInjectHtml} from "./Bean/ViewEventInjectHtml";

export class ViewEventHandler {

    #root = document;
    #_initialized = false;
    #overlay = null;

    constructor(root) {
        this.#overlay = new OverlayHelper();
        this.#root = root;
    }

    init() {
        this.#_initialized = true;
        this.#root.querySelectorAll('[data-event]').forEach(this.#attatchEvents.bind(this));
        return this;
    }


    get isInitialized() {
        return this.#_initialized;
    }

    #attatchEvents(element) {
        if (element && element.matches('[data-event]')) {
            const viewEvent = ViewEvent.factory(element.dataset.event);
            console.debug('Attatched event: ', viewEvent);
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
    #triggerEvent(viewEvent) {
        console.debug('Trigger event: ', viewEvent)
        switch (viewEvent.type) {
            case ViewEvent.TYPE_SUBMIT:
                return this.#triggerSubmit(viewEvent);
            case ViewEvent.TYPE_CALLBACK:
                return this.#triggerCallback(viewEvent);
            case ViewEvent.TYPE_LINK:
                return this.#triggerLink(viewEvent);
            case ViewEvent.TYPE_MODAL:
                return this.#triggerModal(viewEvent);
        }
    }

    /**
     *
     * @param {ViewEvent} viewEvent
     */
    #triggerSubmit(viewEvent) {
        let url = new URL(viewEvent.path, document.baseURI);
        let form = document.getElementById(viewEvent.form);
        let formData = new FormData(form);
        this.#fetchEvent(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-EVENT': JSON.stringify(viewEvent)
            },
            method: form.method,
            body: formData
        });
    }

    /**
     *
     * @param {ViewEvent} viewEvent
     */
    #triggerModal(viewEvent) {
        let url = new URL(viewEvent.path, document.baseURI);
        this.#fetchEvent(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-EVENT': JSON.stringify(viewEvent)
            },
        });
    }

    /**
     *
     * @param {ViewEvent} viewEvent
     */
    #triggerLink(viewEvent) {
        let url = new URL(viewEvent.path, document.baseURI);
        this.#fetchEvent(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-EVENT': JSON.stringify(viewEvent)
            },
        });
    }

    #triggerCallback(viewEvent) {
        this.#triggerLink(viewEvent);
    }

    #fetchEvent(url, options) {
        if (!this.#overlay.isVisible()) {
            this.#overlay.show();
            fetch(url, options)
                .then(response => response.headers.get('Content-Type') === 'application/json' ? response.json() : response.text())
                .then(data => {
                    const response = ViewEventResponse.factory(data);
                    console.debug('Handle response:', response);
                    this.#handleEvent(response)
                    this.#inject(response);
                    this.#handleAttributes(response);
                    this.#overlay.hide();
                })
                .catch(err => console.error(err))
        }
    }


    #handleEvent(data) {
        if (data && data.event) {
            if (data.event.deleteCache === true) {
                window.caches.delete('pars-helper');
                console.debug('Deleted cache');
            }
            console.log('Handle event:', data.event);
            switch (data.event.type) {
                case ViewEvent.TYPE_LINK:
                    return this.#handleLink(data);
                case ViewEvent.TYPE_MODAL:
                    return this.#handleModal(data);
                case ViewEvent.TYPE_SUBMIT:
                    return this.#handleSubmit(data);
                case ViewEvent.TYPE_CALLBACK:
                    return this.#handleCallback(data);
            }
        }
        return data;
    }

    #handleSubmit(data) {
        return data;
    }

    #handleCallback(data) {
        if (data.event.path && data.event.target && data.html) {
            data.inject.html.push(ViewEventInjectHtml.factory({
                mode: 'replace',
                selector: data.event.target,
                html: data.html
            }));
        }
        return data;
    }


    #handleModal(data) {
        return this.#handleLink(data);
        if (data.event.path && data.event.target && data.html) {
            if (data.event.history === true) {
                history.replaceState(data, null, data.event.path);
                history.pushState(data, null, data.event.path);
            }
            document.querySelectorAll('#ajax-modal .modal-body').forEach(body => {
                body.innerHTML = '';
                body.append(HtmlHelper.createElementFromHTML(data.html));
            });
            // todo open modal
        }
        return data;
    }

    #handleLink(data) {
        if (data.event.path && data.event.target && data.html) {
            if (data.event.history === true) {
                history.replaceState(data, null, data.event.path);
                history.pushState(data, null, data.event.path);
            }

            data.inject.html.push(ViewEventInjectHtml.factory({
                mode: 'replace',
                selector: data.event.target,
                html: data.html
            }));
        }
        return data;
    }

    #handleAttributes(data) {
        if (data && data.attributes) {
            console.debug('Handle attributes:', data.attributes);
            if (data.attributes.redirect_url) {
                window.location = data.attributes.redirect_url;
            }
        }
    }

    #inject(data) {
        if (data && data.inject) {
            console.debug('Inject:', data.inject)
            if (data.inject.html) {
                data.inject.html.forEach(html => {
                    document.querySelectorAll(html.selector).forEach(element => {
                        const newElement = HtmlHelper.createElementFromHTML(html.html);
                        switch (html.mode) {
                            case 'replace':
                                element.replaceWith(newElement);
                                break;
                            case 'append':
                                element.append(newElement);
                                break;
                            case 'prepend':
                                element.prepend(newElement);
                                break;
                        }
                        if (newElement.matches('[data-event]')) {
                            this.#attatchEvents(newElement);
                        }
                        newElement.querySelectorAll('[data-event]').forEach(newSubElement => {
                                this.#attatchEvents(newSubElement);
                            }
                        );
                    });
                })
            }
            if (data.inject.script) {
                data.inject.script.forEach(script => {
                    if (!script.unique || document.querySelectorAll('script[src=' + script.script + ']').length === 0) {
                        document.querySelectorAll('body').forEach(element => element.append(HtmlHelper.createElementFromHTML('<script src="' + script.script + '"></script>')));
                    }
                });
            }
        }
    }
}
