Kowri WebPOS Merchant APIs
This Application Programming Interface (API) describes how to integrate with the Kowri Point of Sale (POS)engine for order generation and payment collection as a Merchant (Biller). The Kowri POS API flow diagram demonstrates the use of the APIs in preparing orders and processing payments. 

To Access the Kowri POS services the following are required: 

Web-Service URL

Merchant Access Credentials

App Reference

Secret

App ID


Access Details
Access type	Detail
Webservice Base URL (Live)	https://posapi.kowri.app/
Webservice Base URL (UAT)	https://kbposapi.mykowri.com/
Test Account	Contact Kowri Merchant Acquiring rep for a test account
App Reference	Contact Kowri integration team for app reference to access web-service
Secret	Contact Kowri integration team for secret to access web-service
App ID	Contact Kowri integration team for your App ID

Callback Response
Kowri POS is able to send a callback response to the merchant with details of the transaction, provided a callback URL has been set on the Kowri device. Visit our knowledgebase for more information on how to create devices.

Callback Response Format 

Parameter	Description
status	the status of the payment.
0	Successful
-1	Technical Error
-2	Customer cancelled payment
cust_ref	the merchantOrderId
pay_token	the invoice number generated for the order
transaction_id	the transaction Id generated for the successful payment
Server-to-Server Callbacks
Our API also supports server-to-server callbacks to notify merchants when payment transactions are completed. Callbacks are sent asynchronously after payment processing is finalized, allowing merchants to receive real-time updates about transaction status without polling.

Callback Configuration
Callbacks can be configured in two ways:

1. Pre-configured Server Callback URL: Your preferred callback URL can be sent to our operations team to configure on our backend for you. This URL will then be used for all transactions processed for that service.

2. Per-Request Webhook URL: Include a `webhookUrl` in the payment request metadata. This allows you to specify a different callback URL for individual transactions, overriding the collection's default callback URL.

json

{

json
"metadata": \[
   {
     "key": "webhookUrl",
     "value": "https://your-server.com/payment-callback"
   }
 \]
}

Priority: If a `webhookUrl` is provided in the payment request metadata, it takes precedence over the pre-configured callback URL.


When Callbacks Are Sent
Callbacks are triggered after payment processing is complete, specifically:

- Successful Payments: When the service has been rendered successfully, you will receive a status of `FULFILLED`

- Failed Payments: When the service could not be rendered for some reason, you will receive a status of `UNFULFILLED_ERROR`. Further details of the error will be provided in the `comment` field.

Callbacks are sent asynchronously, so they do not block the payment processing flow.

Callback Payload Structure
The callback payload is sent as a JSON object via HTTP POST to your configured callback URL. The structure includes the following fields:

{
"amount": "5.50",                    // Invoice amount (formatted to 2 decimal places)  
"currency": "GHS",                   // Currency code    
"transactionId": "ZmLabWiwsX",       // Initiator's transaction ID    
"serviceCode": "1512",               // Merchant service code    
"status": "FULFILLED",        // Transaction status: FULFILLED | UNFULFILLED_ERROR    
"success": true,                     // Successful execution flag (boolean)    
"receiptNo": "513775030897",         // Merchant's transaction receipt number    
"orderId": "22fb7509-eacf-4efa-bc8a-aa1a464d2691",  // Merchant's order ID    
"comment": "Payment of GHS5.00 to Your collection", // Payment narration/status message  
"callbackUrl": null,                 // Web redirect URL (if configured)    
"customerReference": null,          // Customer payment reference (e.g., ticket number)  
"paymentChannel": "MTN Mobile Money", // Mode of payment    
"paymentChannelReference": "3711335187",  // Transaction ID from payment processor    
"transactionDate": "2025-11-12T10:16:55"  // Date of processing (ISO format)
}


Additional Fields
Depending on the transaction context, the following additional fields may be included:

- app_id: The application id passed with the original payment request if applicable (included when the order has an associated app reference)

- payerName: Name of the payer

- payerNumber: Phone number of the payer

- paymentAccount: Payment channel account number (phone number for mobile money payments)

Transaction Status Values
The `status` field in the callback payload can have the following values:

- FULFILLED: Payment was successfully processed and completed

- UNFULFILLED_ERROR: Payment processing failed or encountered an error

The `success` field is a boolean that directly indicates whether the transaction was successful (`true` for `FULFILLED`, `false` for `UNFULFILLED_ERROR`).

Callback URL (Redirect)
The `callbackUrl` field in the payload contains the web redirect URL that can be used to redirect users back to the merchant's website after payment. This URL is constructed from the `callbackUrl` provided in the original payment request and includes query parameters:

- Success: `status=0&transac_id={transactionId}&cust_ref={orderId}&pay_token={invoiceNum}`

- Failure: `status=-1&cust_ref={orderId}&pay_token={invoiceNum}`

- Cancelled: `status=-2&cust_ref={orderId}&pay_token={invoiceNum}`

Implementation Notes
- Callbacks are sent asynchronously using HTTP POST requests

- Your callback endpoint should respond promptly (within a reasonable timeout)

- It is recommended to implement idempotency checks in your callback handler to handle duplicate callbacks

- Always verify the transaction status using the `transactionId` or `orderId` before processing the callback

- The callback payload may include additional fields beyond those documented above, depending on the payment channel and transaction context



API Methods
Kowri POS endpoint calls will be made in the format of JSON requests over HTTP POST. The URLs provided in the rest of this documentation are relative to the web service base URL supplied in the access details provided above.

POST
List Payment Options
/webpos/listPayOptions
The List Payment Options Endpoint, list various payment options (mobile money, card, direct bank payments) that have been mapped to the Merchant. 

Parameter List
Parameter	Description	Req	Type
requestId	unique identify for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
HEADERS
appId
{insert assigned app Id here}

Body
raw (json)
json
{
    "requestId": "5468a68a-63e7-45d6-b9ac-1f38d9c14fbc",
    "appReference": "xxxxxxx",
    "secret": "xxxxxxx"
}


Example Request
List Payment Options
http
POST /webpos/listPayOptions HTTP/1.1
Host: 
appId: 123456
Content-Length: 119

{
    "requestId": "91708c74-b05a-424b-96ae-180b626692c4",
    "appReference": "xxxxxx",
    "secret": "xxxxxxxx"
}

Example Response
json
{
  "requestId": "f9cc1609-8c51-4b86-9f47-502a51111f1f",
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": "Payment options retrieved successfully",
  "result": [
    {
      "name": "MTN Mobile Money",
      "description": "MTN Mobile Money",
      "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/mobile_money.png",
      "maxAmount": 0,
      "minAmount": 0,
      "walletRef": "Mobile Number",
      "refRegex": "\\d+",
      "type": "MOBILE_MONEY",
      "provider": "MTN_MONEY",
      "preAuthNotice": "Please ensure you have sufficient funds on in your wallet, otherwise you may not receive the prompt",
      "authNotice": "You should receive a prompt on your phone to enter your PIN. If you don't receive the prompt, dial *170#"
    },
    {
      "name": "Vodafone Cash",
      "description": "Vodafone Cash",
      "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/vf_cash.png",
      "maxAmount": 0,
      "minAmount": 0,
      "walletRef": "Mobile Number",
      "refRegex": "\\d+",
      "type": "MOBILE_MONEY",
      "provider": "VODAFONE_CASH",
      "preAuthNotice": "Please ensure you have sufficient funds on in your wallet, otherwise you may not receive the prompt",
      "authNotice": "You should receive a prompt on your phone to enter your PIN."
    },
    {
      "name": "Slydepay",
      "description": "Slydepay",
      "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/slydepay.png",
      "maxAmount": 0,
      "minAmount": 0,
      "walletRef": "Email or Mobile Number",
      "refRegex": ".*",
      "type": "MOBILE_MONEY",
      "provider": "SLYDEPAY",
      "preAuthNotice": "A code will be generated for you to complete the payment with your Slydepay app",
      "authNotice": "Please scan the QR code or enter the pay code to complete payment"
    },
    {
      "name": "Stanbic Bank",
      "description": "Direct debit of Stanbic bank accounts",
      "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/stanbic.png",
      "maxAmount": 0,
      "minAmount": 0,
      "walletRef": "Account Number",
      "refRegex": ".*",
      "type": "BANK",
      "provider": "STANBIC_BANK",
      "preAuthNotice": null,
      "authNotice": "Please wait while we debit your bank account"
    }
  ]
}


POST
Create Invoice
/webpos/createInvoice
This endpoint enables the creation of an order through the merchant's integrated channels. Merchants can create orders that have sub items or are without items but rather an invoice amount. An order created with items can have an unlimited number of order items.

If you have provided a callback URL in your account settings, Kowri will send a callback response to your application after your customer has completed the payment on the web checkout page.

Parameter List for Create Invoice with Items

Table
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
merchantOrderId	ID of order generated by merchant	Yes	string
currency	currency supported for payment	Yes	string
reference	Customer’s reference with biller (eg. Account number, mobile number)	Yes	string
trustedNum	Customer's mobile money number that will receive payment request. When this value is passed the customer is excluded from Debit Authorisation Checks.	No	String
invoiceItems[]	array of invoice/items. parameter details are italicised below	Yes	array
code	item code	Yes	string
name	item name	Yes	string
description	item description	Yes	string
imgUrl	item image url	Yes	string
unitPrice	item unit price	Yes	float
quantity	item quantity	Yes	int
subTotal	item sub total = unit price x quantity	Yes	float

Parameter List for Create Invoice with No Items
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
merchantOrderId	ID of order generated by merchant	Yes	string
currency	currency supported for payment	Yes	string
amount	invoice amount	Yes	float
reference	invoice reference	Yes	string
HEADERS
appId
{insert assigned app Id here}

Body
raw (json)

{
    "requestId": "67989db2-3c5d-4ea5-a4ec-4a9c967011ea",
    "appReference": "xxxxxxxxxx",
    "secret": "xxxxxxx",
    "merchantOrderId": "2020081014952877",
    "reference":"GHP12345",
    "currency": "GHS",
    "invoiceItems": [{
        "name": "Papilo Goods and Services",
        "description": "Purchase of Goods and services",
        "imgUrl": "https://test.usebillbox.com/static/images/logo/apsu.jpg",
        "unitPrice": 83.15,
        "quantity": 1,
        "subTotal": 83.15
    }]
}

Create Invoice with No Items
http
POST /webpos/createInvoice HTTP/1.1
Host: 
appId: {insert assigned app ID here}}
Content-Length: 252

{
    "requestId": "eca93435-f685-4454-809f-0b0d3c4a6c12",
    "appReference": "xxxxxxx",
    "secret": "xxxxxxx",
    "merchantOrderId": "2020081014952887",
    "currency": "GHS",
     "amount": "7",
    "reference": "Papilo Airtime Service"
}

Example Response
json
{
  "requestId": "683bba8d-6e83-4ec3-a87e-00217345b4dc",
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": "Invoice created successfully",
  "result": {
    "serviceCode": "225",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "merchantOrderId": "2020081014952887",
    "currency": "GHS",
    "itemCount": 0,
    "invoiceAmount": "7",
    "fees": "0",
    "grandTotal": "7",
    "status": "OPEN",
    "callbackUrl": "https://www.dreamoval.com",
    "checkoutUrl": "http://bbwebpos.doersops.com/slydepay/slydepay/b7ee2db3-e743-49c9-8582-9342ac8d0641"
  }
}

POST
Get Invoice Summary
/webpos/getInvoiceSummary
This endpoint retrieves the summary details of an order so it can be displayed on the merchant checkout channel. An order can be retrieved using either the merchants order ID used in creating the order, or the invoice number generated from the create invoice process.

Parameter List for Get Invoice Summary with Merchant Order ID
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
merchantOrderId	ID of order generated by merchant	Yes	string
Parameter List for Get Invoice Summary with Invoice Number
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
invoiceNum	invoice number of order generated by merchant	Yes	string
HEADERS
appId
{insert assigned Id here}

Body
raw (json)

{
    "requestId": "d5b059ff-e2aa-4d09-84e9-3df14f40fe9d",
    "appReference": "digital@dreamoval.com",
    "secret": "1457631844304",
    "invoiceNum": "7868d43d-0c91-4701-a0ff-ff1b7e5e37e5"
}


Example Request
Get Invoice Summary with Merchant Order ID
http
POST /webpos/getInvoiceSummary HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 161

{
    "requestId": "d54ae81a-888d-469e-bc77-dfcc36b7762b",
    "appReference": "loki",
    "secret": "password",
    "merchantOrderId": "2020081014952887"
}


Example Response
json
{
  "requestId": null,
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": "Order retrieved successfully",
  "result": {
    "serviceCode": "225",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "merchantOrderId": "2020081014952887",
    "currency": "GHS",
    "itemCount": 0,
    "invoiceAmount": "7",
    "fees": "0",
    "grandTotal": "7",
    "status": "OPEN",
    "callbackUrl": "https://www.dreamoval.com",
    "checkoutUrl": "http://bbwebpos.doersops.com/slydepay/slydepay/b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "invoiceItems": []
  }
}

Example Request
Get Invoice Summary with Invoice Number
http
POST /webpos/getInvoiceSummary HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 176

{
    "requestId": "fbfd2dd8-1e98-497b-8377-1c12fe2594e2",
    "appReference": "loki",
    "secret": "password",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641"
}
200 OK
Example Response
Body
Headers (13)
View More
json
{
  "requestId": null,
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": "Order retrieved successfully",
  "result": {
    "serviceCode": "225",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "merchantOrderId": "2020081014952887",
    "currency": "GHS",
    "itemCount": 0,
    "invoiceAmount": "7",
    "fees": "0",
    "grandTotal": "7",
    "status": "OPEN",
    "callbackUrl": "https://www.dreamoval.com",
    "checkoutUrl": "http://bbwebpos.doersops.com/slydepay/slydepay/b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "invoiceItems": []
  }
}

POST
Get Invoice
/webpos/getInvoice
This endpoint retrieves the full details of an order so it can be displayed on the Financial institutions checkout channel. The full details of the invoice include merchant profile information, payment options, and charges/fees. An invoice can be retrieved using either the merchants order ID used in creating the invoice, or the invoice number generated from the create invoice process.

Parameter List for Get Invoice with Merchant Order ID
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
merchantOrderId	ID of order generated by merchant	Yes	string
Parameter List for Get Invoice with Invoice Number
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
invoiceNum	invoice number of order generated by merchant	Yes	string
HEADERS
appId
{insert assigned app Id here}

Body
raw (json)
json
{
    "requestId": "d48a22c0-cfb1-4f0a-97ac-5e13c7744a17",
    "appReference": "xxxxxx",
    "secret": "xxxxxxx",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641"
}


Example Request
Get Invoice with Merchant Order ID
http
POST /webpos/getInvoice HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 161

{
    "requestId": "9251b1e2-4aeb-4ef6-a9ab-c21765f0132c",
    "appReference": "xxxxxx",
    "secret": "xxxxxx",
    "merchantOrderId": "2020081014952887"
}

Example Response
json
{
  "requestId": null,
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": "Order retrieved successfully",
  "result": {
    "serviceCode": "225",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "merchantOrderId": "2020081014952887",
    "currency": "GHS",
    "itemCount": 0,
    "invoiceAmount": "7",
    "fees": "0",
    "grandTotal": "7",
    "status": "OPEN",
    "callbackUrl": "https://www.dreamoval.com",
    "checkoutUrl": "http://bbwebpos.doersops.com/slydepay/slydepay/b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "invoiceItems": [],
    "merchantDetails": {
      "name": "Papilo Airtime Services",
      "logo": "./static/images/logo/WhatsApp_Image_2020-06-19_at_11.23.28.jpeg",
      "callbackUrl": "",
      "supportNumber": "",
      "supportEmail": ""
    },
    "payOptions": [
      {
        "name": "MTN Mobile Money",
        "description": "MTN Mobile Money",
        "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/mobile_money.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Mobile Number",
        "refRegex": "\\d+",
        "type": "MOBILE_MONEY",
        "provider": "MTN_MONEY",
        "preAuthNotice": "<p>Please ensure you have sufficient funds on in your wallet, otherwise you may not receive the prompt</p>",
        "authNotice": "<p>Please check your phone for a prompt from MTN Mobile Money, <strong>If you didn't receive a prompt, please follow the instructions below:</strong> </p><ol><li>Dial *170#</li><li>Select 'My Wallet'</li> <li>Select 'My Approvals'</li> <li>Enter your mobile money pin</li> <li>Select the pending transaction to approve</li></ol><p style='color: red !important'><strong>NOTE: </strong>A prompt will not appear if you do not have sufficient funds in your MTN Mobile Money wallet for this transaction.</p>",
        "flowType": "PROMPT",
        "fee": "8.00"
      },
      {
        "name": "Vodafone Cash",
        "description": "Vodafone Cash",
        "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/vf_cash.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Mobile Number",
        "refRegex": "\\d+",
        "type": "MOBILE_MONEY",
        "provider": "VODAFONE_CASH",
        "preAuthNotice": "Please ensure you have sufficient funds on in your wallet, otherwise you may not receive the prompt",
        "authNotice": "You should receive a prompt on your phone to enter your PIN.",
        "flowType": "PROMPT",
        "fee": "0.07"
      },
      {
        "name": "Slydepay",
        "description": "Slydepay",
        "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/slydepay.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Email or Mobile Number",
        "refRegex": ".*",
        "type": "MOBILE_MONEY",
        "provider": "SLYDEPAY",
        "preAuthNotice": "A code will be generated for you to complete the payment with your Slydepay app",
        "authNotice": "Please scan the QR code or enter the pay code to complete payment",
        "flowType": "QR_CODE",
        "fee": "0.00"
      },
      {
        "name": "Stanbic Bank",
        "description": "Direct debit of Stanbic bank accounts",
        "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/stanbic.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Account Number",
        "refRegex": ".*",
        "type": "BANK",
        "provider": "STANBIC_BANK",
        "preAuthNotice": null,
        "authNotice": "Please wait while we debit your bank account",
        "flowType": "DEBIT",
        "fee": "0.14"
      },
      {
        "name": "Visa/Mastercard",
        "description": "Payment using credit/debit card",
        "logo": "http://52.40.100.125:8082/billbox-pos./static/images/logo/visa-mastercard.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "PAN",
        "refRegex": ".*",
        "type": "CARD",
        "provider": "CARD",
        "preAuthNotice": "",
        "authNotice": "Please wait while we redirect you to our card processor",
        "flowType": "REDIRECT",
        "fee": "15.00"
      },
      {
        "name": "AirtelTigo Money",
        "description": "AirtelTigo Money",
        "logo": "http://52.40.100.125:8082/billbox-posnull",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Mobile Number",
        "refRegex": "\\d+",
        "type": "MOBILE_MONEY",
        "provider": "AIRTELTIGO_MONEY",
        "preAuthNotice": "<p>Please ensure you have sufficient funds on in your wallet, otherwise you may not receive the prompt</p>",
        "authNotice": "<p>Please check your phone for a prompt from AirtelTigo Money, <strong>If you didn't receive a prompt, please follow the instructions below:</strong> </p><ol><li>Dial *170#</li><li>Select 'My ",
        "flowType": "PROMPT",
        "fee": "1.00"
      }
    ]
  }
}


Example Request
Get Invoice with Invoice Number
http
POST /webpos/getInvoice HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 177

{
    "requestId": "82201dbf-95c8-4873-8749-82aba448cfff",
    "appReference": "xxxxxx",
    "secret": "xxxxxxx",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641"
}

Example Response
Body
Headers (13)
View More
json
{
  "requestId": null,
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": "Order retrieved successfully",
  "result": {
    "serviceCode": "225",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "merchantOrderId": "2020081014952887",
    "currency": "GHS",
    "itemCount": 0,
    "invoiceAmount": "7",
    "fees": "0",
    "grandTotal": "7",
    "status": "OPEN",
    "callbackUrl": "https://www.dreamoval.com",
    "checkoutUrl": "http://bbwebpos.doersops.com/slydepay/slydepay/b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "invoiceItems": [],
    "merchantDetails": {
      "name": "Papilo Airtime Services",
      "logo": "./static/images/logo/WhatsApp_Image_2020-06-19_at_11.23.28.jpeg",
      "callbackUrl": "",
      "supportNumber": "",
      "supportEmail": ""
    },
    "payOptions": [
      {
        "name": "MTN Mobile Money",
        "description": "MTN Mobile Money",
        "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/mobile_money.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Mobile Number",
        "refRegex": "\\d+",
        "type": "MOBILE_MONEY",
        "provider": "MTN_MONEY",
        "preAuthNotice": "<p>Please ensure you have sufficient funds on in your wallet, otherwise you may not receive the prompt</p>",
        "authNotice": "<p>Please check your phone for a prompt from MTN Mobile Money, <strong>If you didn't receive a prompt, please follow the instructions below:</strong> </p><ol><li>Dial *170#</li><li>Select 'My Wallet'</li> <li>Select 'My Approvals'</li> <li>Enter your mobile money pin</li> <li>Select the pending transaction to approve</li></ol><p style='color: red !important'><strong>NOTE: </strong>A prompt will not appear if you do not have sufficient funds in your MTN Mobile Money wallet for this transaction.</p>",
        "flowType": "PROMPT",
        "fee": "8.00"
      },
      {
        "name": "Vodafone Cash",
        "description": "Vodafone Cash",
        "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/vf_cash.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Mobile Number",
        "refRegex": "\\d+",
        "type": "MOBILE_MONEY",
        "provider": "VODAFONE_CASH",
        "preAuthNotice": "Please ensure you have sufficient funds on in your wallet, otherwise you may not receive the prompt",
        "authNotice": "You should receive a prompt on your phone to enter your PIN.",
        "flowType": "PROMPT",
        "fee": "0.07"
      },
      {
        "name": "Slydepay",
        "description": "Slydepay",
        "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/slydepay.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Email or Mobile Number",
        "refRegex": ".*",
        "type": "MOBILE_MONEY",
        "provider": "SLYDEPAY",
        "preAuthNotice": "A code will be generated for you to complete the payment with your Slydepay app",
        "authNotice": "Please scan the QR code or enter the pay code to complete payment",
        "flowType": "QR_CODE",
        "fee": "0.00"
      },
      {
        "name": "Stanbic Bank",
        "description": "Direct debit of Stanbic bank accounts",
        "logo": "http://52.40.100.125:8082/billbox-pos/static/images/logo/stanbic.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Account Number",
        "refRegex": ".*",
        "type": "BANK",
        "provider": "STANBIC_BANK",
        "preAuthNotice": null,
        "authNotice": "Please wait while we debit your bank account",
        "flowType": "DEBIT",
        "fee": "0.14"
      },
      {
        "name": "Visa/Mastercard",
        "description": "Payment using credit/debit card",
        "logo": "http://52.40.100.125:8082/billbox-pos./static/images/logo/visa-mastercard.png",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "PAN",
        "refRegex": ".*",
        "type": "CARD",
        "provider": "CARD",
        "preAuthNotice": "",
        "authNotice": "Please wait while we redirect you to our card processor",
        "flowType": "REDIRECT",
        "fee": "15.00"
      },
      {
        "name": "AirtelTigo Money",
        "description": "AirtelTigo Money",
        "logo": "http://52.40.100.125:8082/billbox-posnull",
        "maxAmount": 0,
        "minAmount": 0,
        "walletRef": "Mobile Number",
        "refRegex": "\\d+",
        "type": "MOBILE_MONEY",
        "provider": "AIRTELTIGO_MONEY",
        "preAuthNotice": "<p>Please ensure you have sufficient funds on in your wallet, otherwise you may not receive the prompt</p>",
        "authNotice": "<p>Please check your phone for a prompt from AirtelTigo Money, <strong>If you didn't receive a prompt, please follow the instructions below:</strong> </p><ol><li>Dial *170#</li><li>Select 'My ",
        "flowType": "PROMPT",
        "fee": "1.00"
      }
    ]
  }
}


POST
Cancel Invoice
/webpos/cancelInvoice
This endpoint cancels an order. Cancelling an order modifies the status of the order to CANCELLED preventing customers from paying for it.

Parameter List for Cancel Invoice
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
merchantOrderId	ID of order generated by merchant	Yes	string
HEADERS
appId
{insert assigned app Id here}

Body
raw (json)
json
{
    "requestId": "60a20139-3d99-4800-93d2-c44e39eec49f",
    "appReference": "xxxxxx",
    "secret": "xxxxxxx",
    "merchantOrderId": "2020081014952887"
}

Example Request
Cancel Invoice
http
POST /webpos/cancelInvoice HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 164

{
    "requestId": "2d328f75-d0e7-4187-bb32-597a4fce272b",
    "appReference": "xxxxxxx",
    "secret": "xxxxxxxx",
    "merchantOrderId": "2020081014952887"
}
200 OK
Example Response
Body
Headers (13)
View More
json
{
  "requestId": null,
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": "Order cancelled successfully",
  "result": {
    "serviceCode": "225",
    "invoiceNum": "b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "merchantOrderId": "2020081014952887",
    "currency": "GHS",
    "itemCount": 0,
    "invoiceAmount": "7",
    "fees": "0",
    "grandTotal": "7",
    "status": "CANCELLED",
    "callbackUrl": "https://www.dreamoval.com?status=-2&&cust_ref=2020081014952887&pay_token=b7ee2db3-e743-49c9-8582-9342ac8d0641",
    "checkoutUrl": "http://bbwebpos.doersops.com/slydepay/slydepay/b7ee2db3-e743-49c9-8582-9342ac8d0641"
  }
}

POST
Process Payment
/webpos/processPayment
This endpoint triggers the payment processing of an existing OPEN order. For Mobile money wallet payments, this endpoint pushes a payment request to the customer's phone via a prompt or approval. Card payments are handled on a dedicated PCI compliant processing page that can be accessed via the secure card form link provided in the response.

You can create an order now and then call the Process Payment API later for the customer to complete payment.

Parameter List for Process Payment with invoice number
Table
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
invoiceNum	invoice number	Yes	string
provider	CARD, MTN_MONEY, VODAFONE_CASH, AIRTELTIGO_MONEY	Yes	string
walletRef	Account/wallet number of customer making paying. Account/wallet to be debited. For card payments field is left empty	Yes	string
customerName	Name of customer making payment.	Yes	string
customerMobile	mobile number of customer making payment	Yes	string

HEADERS
appId
1@Je[kR%9v7iROO

Body
raw (json)

json
{
    "appReference": "xxxxxxx",
    "secret": "xxxxxxx",
    "requestId": "cf58fec1-566f-47ce-81b3-18c217e90d58",
    "invoiceNum": "4b1ec1b1-62c9-4483-81b3-ba46b67eef33",
    "transactionId": "",
    "provider": "MTN_MONEY",
    "walletRef": "233541055455",
    "customerName": "Kofi Asamoah",
    "customerMobile": "233261055455"
}

Example Request
Process Payment of Closed Invoice
View More
http
POST /webpos/processPayment HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 345
{
    "appReference": "xxxxxxx",
    "secret": "xxxxxx",
    "requestId": "e3733c27-1533-44ca-9ed1-61b552a91f62",
    "invoiceNum": "7868d43d-0c91-4701-a0ff-ff1b7e5e37e5",
    "transactionId": "",
    "provider": "MTN_MONEY",
    "walletRef": "233541055455",
    "customerName": "Kofi Asamoah",
    "customerMobile": "233261055455"
}

Example Response
Body
Headers (14)
json
{
  "requestId": null,
  "success": false,
  "statusCode": "INVALID_REQUEST",
  "statusMessage": "Only OPEN orders can be paid for",
  "result": null
}


POST
PayNow
/webpos/payNow
The payNow endpoint is a combination of the create invoice and process endpoints, meaning the endpoint creates an order and sends a payment request all in a single call.

This endpoint is useful for merchants to simply push payment requests to their customer's phones, via a prompt, without redirecting the customer to a web page.

Do note that for card payments, you are required to redirect your customer to the secure card form link provided in the response.

Note: The requestId must be unique with each request.

Parameter List for Striaght Pay Now


Table
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
transactionId	transaction ID	Yes	string
provider	CARD, MTN_MONEY, VODAFONE_CASH, AIRTELTIGO_MONEY	Yes	string
walletRef	Account/wallet number of customer making paying. Account/wallet to be debited. For card payments field is left empty	Yes	string
customerName	Name of customer making payment.	Yes	string
customerMobile	mobile number of customer making paymenty	Yes	string
serviceCode	merchant service code	Yes	string
reference	Customer’s reference with biller (eg. Account number, mobile number)	Yes	string

Body
raw (json)

{
    "requestId": "999e06eb-e34b-4ede-a3a5-d5a2ce0629ba",
    "appReference": "xxxxxx",
    "secret": "xxxxxxxx",
    "amount": 10,
    "currency": "GHS",
    "customerName": "Kofi Asamoah",
    "customerSegment": "Test",
    "reference": "APSU 1",
    "transactionId": "",
    "provider": "MTN_MONEY",
    "walletRef": "233244733999",
    "customerMobile": "0208418857"
}

Example Request
PayNow - MoMo

http
POST /webpos/payNow HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 387

{
    "requestId": "002e4fe7-8246-4641-ba1b-7088e569cf1b",
    "appReference": "xxxxxxxx",
    "secret": "xxxxxxx",
    "amount": 10,
    "currency": "GHS",
    "customerName": "Kofi Asamoah",
    "customerSegment": "Test",
    "reference": "APSU 1",
    "transactionId": "",
    "provider": "MTN_MONEY",
    "walletRef": "233244733999",
    "customerMobile": "0208418857"
}

Example Response
Body
Headers (14)
View More
json

{
  "requestId": "c4c5a7df-9817-40ab-9db4-c45c68b47a74",
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": null,
  "result": {
    "walletTransId": "",
    "walletAccountNo": null,
    "callerName": null,
    "status": "PENDING",
    "callerAccountNo": "233555551816",
    "callerTransId": "7976363143",
    "creditDebitFlag": "D",
    "timestamp": null
  }
}



Example Request
PayNow - Card
View More
http
POST /webpos/payNow HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 368

POST /webpos/payNow HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 368

{
    "requestId": "3b4a1f79-da4b-4037-a079-04f253ec5f05",
    "appReference": "xxxxxxx",
    "secret": "xxxxxxxx",
    "amount": 10,
    "currency": "GHS",
    "customerName": "Dolly Kyei",
    "customerSegment": "Test",
    "reference": "TEST 1",
    "transactionId": "",
    "provider": "CARD",
    "walletRef": "",
    "customerMobile": "0208418857"
}


Example Response
Body
Headers (0)
View More
json

{
  "requestId": "88e0c85f-2636-47f0-8681-44e19078f4ef",
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": null,
  "result": {
    "status": "PENDING",
    "token": "aa01c78b-7be8-41ca-ae25-76e38f98d9e1",
    "reference": "2b60b052-9a34-4197-a447-b1a891ba6129",
    "redirectUrl": "https://cardhandler.usebillbox.com/secure/card/capture?pay_token=aa01c78b-7be8-41ca-ae25-76e38f98d9e1"
  }
}


POST
Check Payment Status
/webpos/checkPaymentStatus
This endpoint checks the payment status of an order.

After the payNow request is made, it is ideal to wait for about 30 seconds before making the check status call. After, subsequent check status calls can me made every minute until a success response is returned.

Note: The requestId must be unique with each request.

Parameter List for Create Invoice with Items
Parameter	Description	Req	Type
requestId	unique identifier for every request made via the endpoint	Yes	string
appReference	Merchant API APP Reference	Yes	string
secret	Merchant API APP Secret	Yes	string
transactionId	ID of transaction	Yes	string
HEADERS
appId
{insert assigned app Id here}

Body
raw (json)
json
{
    "requestId": "9c05f8da-64f9-4e64-b0b8-ab3b26ec7bc5",
    "appReference": "xxxxxxxx",
    "secret": "xxxxxxxxx",
    "transactionId": ""
}

Check Payment Status
http
POST /webpos/checkPaymentStatus HTTP/1.1
Host: 
appId: {insert assigned app Id here}
Content-Length: 146

{
    "requestId": "68e0cf00-e05a-465f-a3af-ef2ae82c5f70",
    "appReference": "xxxxxx",
    "secret": "xxxxxxx",
    "transactionId": ""
}


Example Response
Body
Headers (13)
View More
json

{
  "requestId": "ef0ed6d9-da27-4230-8a5f-660cbe051819",
  "success": true,
  "statusCode": "SUCCESS",
  "statusMessage": null,
  "result": {
    "amount": "10.34",
    "currency": "GHS",
    "transactionId": "e3b6c2fb-6e7f-4c21-89a7-db18ced5f8b1",
    "serviceCode": "225",
    "status": "FAILED",
    "success": false,
    "receiptNo": null,
    "orderId": "1614878292648143",
    "comment": null,
    "callbackUrl": "https://www.dreamoval.com?status=-1&&cust_ref=1614878292648143&pay_token=f28ebcb0-9bf6-4d11-a107-a2e10984e8ef"
  }
}