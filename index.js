import {ViewEventHandler} from "./src/View/Event/ViewEvent/ViewEventHandler";

window.viewEventHandler = new ViewEventHandler(document);

document.addEventListener("DOMContentLoaded", () => {
    window.viewEventHandler.init();
});
