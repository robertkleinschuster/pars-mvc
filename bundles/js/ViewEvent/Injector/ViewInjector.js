import {HtmlHelper} from "../Helper/HtmlHelper";
import {ViewEventInject} from "../Bean/ViewEventInject";
import {ViewEvent} from "../Bean/ViewEvent";

export class ViewInjector {
    #root: ParentNode = null;
    listeners = [];

    constructor(root: ParentNode) {
        this.#root = root;
    }

    inject(data: ViewEventInject, event: ViewEvent): void {
        console.debug('Inject:', data)
        if (data.html) {
            data.html.forEach(html => {
                this.#root.querySelectorAll(html.selector).forEach(element => {
                    const newElement = HtmlHelper.createElementFromHTML(html.html);
                    console.debug('Inject element:', {
                        mode: html.mode,
                        element: newElement,
                    })
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
                    this.listeners.forEach(listener => {
                        console.debug('Execute listener:', listener.name, {
                            listener: listener,
                            element: newElement,
                            event: event,
                        });
                        listener(newElement, event);
                    });
                });
            })
        }

        if (data.script) {
            data.script.forEach(script => {
                if (!script.unique || this.#root.querySelectorAll('script[src="' + script.src + '"]').length === 0) {
                    const scriptElement = HtmlHelper.createScript(script.src);
                    console.debug('Inject script:', scriptElement)
                    if (this.#root.body) {
                        this.#root.body.append(scriptElement);
                    } else {
                        this.#root.append(scriptElement);
                    }
                }
            });
        }

    }
}
