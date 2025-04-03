@for(index: invoice of invoices) {
    Invoice No: {{ index }}
    Invoice ID: {{ invoice.id }}
    Amount:     {{ invoice.amount }}
}

@for(key: value of invoices[0]) {

    <p><b>{{ field }}</b>: {{ value }}</p>
}  

<br/>
