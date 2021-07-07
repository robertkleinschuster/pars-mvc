import {ViewEvent} from "../Bean/ViewEvent";
import {SubmitHandler} from "./SubmitHandler";
import {CallbackHandler} from "./CallbackHandler";
import {LinkHandler} from "./LinkHandler";
import {ModalHandler} from "./ModalHandler";
import {AbstractHandler} from "./AbstractHandler";
import {RefreshFormHandler} from "./RefreshFormHandler";

export class HandlerFactory {
    static create(root, event: ViewEvent): AbstractHandler {
        switch (event.type) {
            case ViewEvent.TYPE_SUBMIT:
                return new SubmitHandler(root, event);
            case ViewEvent.TYPE_REFRESH_FORM:
                return new RefreshFormHandler(root, event);
            case ViewEvent.TYPE_CALLBACK:
                return new CallbackHandler(root, event);
            case ViewEvent.TYPE_LINK:
                return new LinkHandler(root, event);
            case ViewEvent.TYPE_MODAL:
                return new ModalHandler(root, event);
        }
    }
}
