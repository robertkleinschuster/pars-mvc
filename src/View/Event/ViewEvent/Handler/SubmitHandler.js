import {AbstractHandler} from "./AbstractHandler";
import {ViewEvent} from "../Bean/ViewEvent";

export class SubmitHandler extends AbstractHandler {

    _buildFetchOptions(viewEvent: ViewEvent): RequestInit {
        let form = this._root.querySelector('#' + this._event.form);
        let formData = new FormData(form);
        return {
            headers: {
                'pars-ajax': 'true',
                'pars-view-event-data': JSON.stringify(this._event)
            },
            method: form.method,
            body: formData
        }
    }
}
