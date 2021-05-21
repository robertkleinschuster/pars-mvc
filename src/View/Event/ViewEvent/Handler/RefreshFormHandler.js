import {AbstractHandler} from "./AbstractHandler";
import {ViewEvent} from "../Bean/ViewEvent";
import {ParameterHelper} from "../Helper/ParameterHelper";

export class RefreshFormHandler extends AbstractHandler {

    _buildFetchUrl(viewEvent: ViewEvent): string {
        let url = new URL(viewEvent.path, document.baseURI);
        //let form = this._root.querySelector('#' + this._event.form);
        let form = document.forms.namedItem(this._event.form);
        let formData = new FormData(form);
        let dataParameter = new ParameterHelper('data')
        for (let [key, value] of formData.entries()) {
            dataParameter.setAttributes(key, value);
        }
        url.searchParams.append('data', dataParameter.toString());
        return url.toString();
    }
}
