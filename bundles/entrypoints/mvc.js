import {ViewEventHandler} from "../js/ViewEvent/ViewEventHandler";
window.eventHelper = new ViewEventHandler(document);

document.addEventListener("DOMContentLoaded", () => {
    window.eventHelper.init();
});
