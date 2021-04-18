export class ViewBean {

    /**
     *
     * @param {object} object
     */
    fromObject(object) {
        for (const [key, value] of Object.entries(object)) {
            this[key] = value;
        }
        return this;
    }

    /**
     *
     * @param  {string|object} data
     * @return {this}
     */
    static factory(data) {
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }
        const result = new this(data);
        result.fromObject(data);
        return result;
    }
}
