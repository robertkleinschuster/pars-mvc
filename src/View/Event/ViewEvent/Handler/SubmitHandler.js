import {AbstractHandler} from "./AbstractHandler";
import {ViewEvent} from "../Bean/ViewEvent";

export class SubmitHandler extends AbstractHandler {

    _buildFetchOptions(viewEvent: ViewEvent): RequestInit {
        let form = this._root.querySelector('#' + this._event.form);
        let formData = new FormData(form);
        formData.set('pars-view-event-data', JSON.stringify(viewEvent))
        return {
            headers: {'pars-ajax': 'true'},
            method: form.method,
            body: formData
        }
    }
}
