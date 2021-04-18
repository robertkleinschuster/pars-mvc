export class PathHelper {
    base;
    parameters;

    constructor() {
        let that = this;
        this.parameters = [];
        this.base = window.location.pathname;
        let search = window.location.search;
        if (search.length) {
            search = search.substring(1);
            search.split('&').forEach(part => {
                let name = part.split('=')[0]
                let parameterStr = part.split('=')[1];
                if (parameterStr.length) {
                    let param = new Parameter(name);
                    param.fromString(parameterStr);
                    that.parameters.push(param);
                }
            });
        }
    }

    addParameter(parameter) {
        this.parameters = this.parameters.filter(item => item.name !== parameter.name);
        this.parameters.push(parameter);
    }

    getPath() {
        let str = '?';
        let length = this.parameters.length;
        this.parameters.forEach((param, index) => {
            str += param.name + '=' + param.toString();
            if (index <= length - 2) {
                str += '&';
            }
        });
        return str;
    }
}
