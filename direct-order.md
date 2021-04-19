I) USER
1) User goes to Direct Order
2) User enters Order Numbers / amounts
3) User selects a supplier
4) User clicks calculate
5) Info gets submitted

II) BACKEND
1) Backend gets orderSource for orderNumber/supplier for each position
2) Backend gets additional orderSources for the found materials
3) Backend gets current price and availability from the supplier's website
4) Backend sends an email to the user, that all data is available

III) USER
1) User requests the result
2) For each position user selects one supplier/order Source
3) User creates order for each supplier

PostMan Routes:

Login Post:
https://localhost:8000/api/auth/login

Body:
{
    "email":"logins@steffengrell.de",
    "password":"test123"
}

Request Direct Order:
https://localhost:8000/api/materialorders/directorder

Body:
{
    "orderPositions":[
        {"orderNumber":"PPB28","amount":10},
        {"orderNumber":"HVR1580D","amount":3},
        {"orderNumber": "QSVI1RTK", "amount": 2}
    ],
    "supplierId":"TAKE ID OF GC SUPPLIER"
}
