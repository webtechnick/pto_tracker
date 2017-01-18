import Errors from './errors.js';

class Form {
    constuctor(data) {
        this.originalData = data;
        this.errors = new Errors();

        for (let field in data) {
            this.field = data.field;
        }
    }
}


export default Form;