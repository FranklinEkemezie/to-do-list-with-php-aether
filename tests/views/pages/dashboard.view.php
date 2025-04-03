Welcome, {{ name }}!
Email: {{ email }}
ID: USER_{{ user.id }}

@{
    for(invoice of invoices) {
        <p>Invoice ID: {{ invoice.id }}</p>
    }
}}

@{
    for(key: value of bio_info) {
        {{ key }}: {{ 
    }
}}
