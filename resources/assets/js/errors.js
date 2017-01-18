class Errors {
    constuctor() {
        this.errors = {};
    }

    get(field) {
        if (this.errors[field]) {
            return this.errors[field][0];
        }
    }

    has(field) {
        return this.errors.hasOwnProperty(field);
    }

    set(errors) {
        this.errors = errors;
    }

    clear(field) {
        delete this.errors[field];
    }
}

export default Errors;