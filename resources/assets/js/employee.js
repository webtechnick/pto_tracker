class Employee {
    constructor(data) {
        this.originalData = data;
        for (let field in data) {
            this[field] = data[field];
        }
    }

    renderSmall() {
        let letter = this.name[0];
        return this.render(letter);
    }

    render(text) {
        text |= this.name;
        console.log(this.bgcolor);
        return `<span class="day-pto" style="background-color:${this.bgcolor}; color: ${this.color};">${text}</span>`;
    }
}