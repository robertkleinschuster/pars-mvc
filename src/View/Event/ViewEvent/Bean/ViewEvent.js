import {ViewBean} from "../../Bean/ViewBean";

export class ViewEvent extends ViewBean {
    type = null;
    id = null;
    trigger = null;
    target = null;
    delegate = null;
    form = null;
    path = null;
    history = false;
    deleteCache = false;

    static get TYPE_LINK()
    {
        return 'link';
    }

    static get TYPE_MODAL()
    {
        return 'modal';
    }

    static get TYPE_SUBMIT()
    {
        return 'submit';
    }
    static get TYPE_REFRESH_FORM()
    {
        return 'refresh_form';
    }

    static get TYPE_CALLBACK()
    {
        return 'callback';
    }
}
