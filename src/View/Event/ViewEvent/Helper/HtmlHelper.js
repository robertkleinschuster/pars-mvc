export class HtmlHelper
{
    /**
     *
     * @param htmlString
     * @returns {ChildNode}
     */
    static createElementFromHTML(htmlString): HTMLElement {
        const div = document.createElement('div');
        div.innerHTML = htmlString.trim();
        return div.firstChild;
    }

    /**
     *
     * @param src
     * @returns {HTMLScriptElement}
     */
    static createScript(src: string): HTMLScriptElement {
        const script = document.createElement("script");
        script.src = src;
        return script;
    }


}
