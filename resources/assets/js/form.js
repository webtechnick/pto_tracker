import Errors from './errors.js';

class Form {
    constructor(data) {
        this.originalData = data;
        this.errors = new Errors();

        for (let field in data) {
            this[field] = data[field];
        }
    }

    data() {
        let retval = {};
        for (let field in this.originalData) {
            retval[field] = this[field];
        }
        return retval;
    }

    submit() {
        axios.post('/ptos/store', this.data())
             .then(this.onSuccess)
             .catch(this.onFailure);
    }

    onSuccess(response) {
        console.log(response);
    }

    onFailure(errors) {
        console.log(errors);
        //this.errors.set(errors.response.data);
        //console.log(errors.response.data);
    }
}


export default Form;