export class ParameterHelper {
    attributes;
    name;

    constructor(name) {
        this.attributes = [];
        this.name = name;
    }

    setAttributes(key, value = '') {
        this.attributes.push({
            key: key,
            value: value
        });
        return this;
    }

    fromString(data) {
        let that = this;
        data = decodeURIComponent(data);
        data.split(';').forEach(item => {
            let key = item.split(':')[0];
            let value = item.split(':')[1];
            if (typeof value !== 'undefined' && value.length) {
                that.setAttributes(key, value);
            } else {
                that.setAttributes(key);
            }
        });
        return this;
    }

    toString() {
        let str = '';
        let length = this.attributes.length;
        this.attributes.forEach((item, index) => {
                if (item.value.length) {
                    str += item.key + ':' + item.value;
                    if (index <= length - 2) {
                        str += ';';
                    }
                } else {
                    str += item.key;
                }
            }
        )
        return encodeURIComponent(str);
    }
}
