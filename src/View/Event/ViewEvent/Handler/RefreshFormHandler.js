import {AbstractHandler} from "./AbstractHandler";
import {ViewEvent} from "../Bean/ViewEvent";
import {ParameterHelper} from "../Helper/ParameterHelper";

export class RefreshFormHandler extends AbstractHandler {

    _buildFetchOptions(viewEvent: ViewEvent): RequestInit {
        let options = super._buildFetchOptions(viewEvent);
        options.body.set('data', this._buildDataParameter().toString());
        return options;
    }

    _buildDataParameter(): ParameterHelper
    {
        let form = document.forms.namedItem(this._event.form);
        let formData = new FormData(form);
        let dataParameter = new ParameterHelper('data')
        for (let [key, value] of formData.entries()) {
            dataParameter.setAttributes(key, value);
        }
        return dataParameter;
    }
}
