import {ViewEventHandler} from "../js/ViewEvent/ViewEventHandler";
window.viewEventHandler = new ViewEventHandler(document);

document.addEventListener("DOMContentLoaded", () => {
    window.viewEventHandler.init();
});
